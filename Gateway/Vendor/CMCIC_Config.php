<?php

use Cmsbox\Cmcic\Gateway\Processor\Connector;
/***************************************************************************************
* Warning !! CMCIC_Config contains the key, you have to protect this file with all     *   
* the mechanism available in your development environment.                             *
* You may for instance put this file in another directory and/or change its name       *
***************************************************************************************/

define ("CMCIC_CLE", $config->base[Connector::KEY_ACCOUNT_KEY]);
define ("CMCIC_TPE", $config->base[Connector::KEY_ACCOUNT_TPE]);
define ("CMCIC_VERSION", $config->base[Connector::KEY_ACCOUNT_VERSION]);
define ("CMCIC_SERVEUR", $config->params[$methodId]['api_url_' . $config->base[Connector::KEY_ENVIRONMENT] . '_charge']);
define ("CMCIC_CODESOCIETE", $config->base[Connector::KEY_ACCOUNT_CODE]);
define ("CMCIC_URLOK", $config->storeManager->getStore()->getBaseUrl()
. Core::moduleId() . '/' . $config->params[$methodId][Core::KEY_NORMAL_RETURN_URL]);
define ("CMCIC_URLKO", $config->storeManager->getStore()->getBaseUrl()
. Core::moduleId() . '/' . $config->params[$methodId][Core::KEY_NORMAL_RETURN_URL]);
?>
