<?php
/**
 * Copyright 2017 Google Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
/**
 * For instructions on how to run the full sample:
 *
 * @see https://github.com/GoogleCloudPlatform/php-docs-samples/tree/main/iap/README.md
 */

# Imports Google auth libraries for IAP validation

require_once(DOKU_INC.'vendor-wisvch/autoload.php');
use Google\Auth\AccessToken;

/**
 * Validate a JWT passed to your App Engine app by Identity-Aware Proxy.
 *
 * @param string $iapJwt The contents of the X-Goog-IAP-JWT-Assertion header.
 * @param string $cloudProjectNumber The project *number* for your Google
 *     Cloud project. This is returned by 'gcloud projects describe $PROJECT_ID',
 *     or in the Project Info card in Cloud Console.
 * @param string $cloud_project Your Google Cloud Project ID.
 *
 * @return (user_id, user_email).
 */
function validate_jwt_from_app_engine($iapJwt, $cloudProjectNumber, $cloudProjectId)
{
    $expectedAudience = sprintf(
        '/projects/%s/apps/%s',
        $cloudProjectNumber,
        $cloudProjectId
    );
    return validate_jwt($iapJwt, $expectedAudience);
}

/**
 * Validate a JWT passed to your Compute / Container Engine app by Identity-Aware Proxy.
 *
 * @param string $iapJwt The contents of the X-Goog-IAP-JWT-Assertion header.
 * @param string $cloudProjectNumber The project *number* for your Google
 *     Cloud project. This is returned by 'gcloud projects describe $PROJECT_ID',
 *     or in the Project Info card in Cloud Console.
 * @param string $backendServiceId The ID of the backend service used to access the
 *     application. See https://cloud.google.com/iap/docs/signed-headers-howto
 *     for details on how to get this value.
 */
function validate_jwt_from_compute_engine($iapJwt, $cloudProjectNumber, $backendServiceId)
{
    $expectedAudience = sprintf(
        '/projects/%s/global/backendServices/%s',
        $cloudProjectNumber,
        $backendServiceId
    );
    validate_jwt($iapJwt, $expectedAudience);
}

/**
 * Validate a JWT passed to your app by Identity-Aware Proxy.
 *
 * @param string $iapJwt The contents of the X-Goog-IAP-JWT-Assertion header.
 * @param string $expectedAudience The expected audience of the JWT with the following formats:
 *     App Engine:     /projects/{PROJECT_NUMBER}/apps/{PROJECT_ID}
 *     Compute Engine: /projects/{PROJECT_NUMBER}/global/backendServices/{BACKEND_SERVICE_ID}
 */
function validate_jwt($iapJwt, $expectedAudience)
{   
    // Validate the signature using the IAP cert URL.
    $token = new AccessToken();
    $jwt = $token->verify($iapJwt, [
        'certsLocation' => AccessToken::IAP_CERT_URL
    ]);

    if (!$jwt) {
        throw new Exception('Failed to validate JWT: Invalid JWT');
    }

    $expectedAudiences = explode(',', $expectedAudience);
    if (!in_array($jwt['aud'], $expectedAudiences)) {
        throw new Exception('Invalid audience');
    }

    // Validate token by checking issuer and audience fields.
    assert($jwt['iss'] == 'https://cloud.google.com/iap');

    assert(in_array($jwt['aud'], $expectedAudiences));  

    return $jwt;
}