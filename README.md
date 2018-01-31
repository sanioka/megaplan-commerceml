# megaplan-commerceml

This module for site/landing to send leads to megaplan directly.

Based on:

https://dev.megaplan.ru

https://help.megaplan.ru/API_bibles

https://help.megaplan.ru/API_online_store

#### Before using, set up auth data 

/resourses/params.php
(see example file).

#### v1 CommerceML integration (from official package)

Run /example-commerceml.php script.

— Cannot send phone or email.

— Cannot change client/deal manager.
 
— Cannot choise deal scheme (if more 1 scheme in megaplan). 


#### v2 Direct API integration (custom code, based on official API)

Run /example-api-integration.php script.

Everything works correctly!