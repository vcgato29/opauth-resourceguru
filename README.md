opauth-resourceguru
===================

ResourceGuru strategy for opauth

Opauth-ResourceGuru
=============
[Opauth][1] strategy for Facebook authentication.

Implementation based on https://github.com/resourceguru/api-docs/blob/master/sections/authentication.md

Getting started
----------------
1. Install Opauth-ResourceGuru:
   ```bash
   cd path_to_opauth/Strategy
   git clone https://github.com/wsl/opauth-resourceguru.git ResourceGuru
   ```

2. Create ResourceGuru application at https://developers.resourceguruapp.com/
   ResourceGuru only supports https so if you don't use that on your dev instance use             
   'redirect_uri'=> 'https://' in your config and remove the s in address when redirected back,

3. Configure Opauth-Facebook strategy with at least `client_id` and `client_secret` and  'response_type' => 'code' 

4. Direct user to `http://path_to_opauth/resourceguru` to authenticate

Strategy configuration
----------------------

Required parameters:

```php
<?php
'ResourceGuru' => array(
	'client_id' => 'YOUR CLIENT ID',
	'client_secret' => 'YOUR CLIENT SECRET',
	'response_type' => 'code',
)
```


License
---------
Opauth-ResourceGuru is MIT Licensed  
Copyright © 2012 Wessel Louwris (http://www.techtribe.nl)

[1]: https://github.com/opauth/opauth
