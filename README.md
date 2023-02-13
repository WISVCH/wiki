# WISVCH DokuWiki
A DokuWiki image with configuration for W.I.S.V. Christiaan Huygens. Automatically installs OAuth plugin and links it with `connect.ch.tudelft.nl`.


## Setup
1. Setup a volume claim for `/var/www/dokuwiki/data`
2. Configure OAuth environment variables `WISVCH_CONNECT_CLIENT_ID` and `WISVCH_CONNECT_CLIENT_SECRET`.