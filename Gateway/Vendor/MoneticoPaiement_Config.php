<?php
use Cmsbox\Monetico\Gateway\Processor\Connector;
use Cmsbox\Monetico\Gateway\Config\Core;

/***************************************************************************************
* Warning !! MoneticoPaiement_Config contains the key, you have to protect this file with all     *   
* the mechanism available in your development environment.                             *
* You may for instance put this file in another directory and/or change its name       *
***************************************************************************************/

define ("MONETICOPAIEMENT_KEY", $config->base[Connector::KEY_ACCOUNT_KEY . '_' . $config->base[Connector::KEY_ENVIRONMENT]]);
define ("MONETICOPAIEMENT_EPTNUMBER", $config->base[Connector::KEY_ACCOUNT_TPE]);
define ("MONETICOPAIEMENT_VERSION", $config->base[Connector::KEY_ACCOUNT_VERSION]);
define ("MONETICOPAIEMENT_URLSERVER", $config->params[$methodId]['api_url_' . $config->base[Connector::KEY_ENVIRONMENT] . '_charge']);
define ("MONETICOPAIEMENT_COMPANYCODE", $config->base[Connector::KEY_ACCOUNT_CODE]);
define ("MONETICOPAIEMENT_URLOK", $config->storeManager->getStore()->getBaseUrl()
. Core::moduleId() . '/' . $config->params[$methodId][Core::KEY_NORMAL_RETURN_URL]);
define ("MONETICOPAIEMENT_URLKO", $config->storeManager->getStore()->getBaseUrl()
. Core::moduleId() . '/' . $config->params[$methodId][Core::KEY_NORMAL_RETURN_URL]);

define ("MONETICOPAIEMENT_CTLHMAC","V4.0.sha1.php--[CtlHmac%s%s]-%s");
define ("MONETICOPAIEMENT_CTLHMACSTR", "CtlHmac%s%s");
define ("MONETICOPAIEMENT_PHASE2BACK_RECEIPT","version=2\ncdr=%s");
define ("MONETICOPAIEMENT_PHASE2BACK_MACOK","0");
define ("MONETICOPAIEMENT_PHASE2BACK_MACNOTOK","1\n");
define ("MONETICOPAIEMENT_PHASE2BACK_FIELDS", "%s*%s*%s*%s*%s*%s*%s*%s*%s*%s*%s*%s*%s*%s*%s*%s*%s*%s*%s*%s*");
define ("MONETICOPAIEMENT_PHASE1GO_FIELDS", "%s*%s*%s%s*%s*%s*%s*%s*%s*%s*%s*%s*%s*%s*%s*%s*%s*%s*%s*%s");
define ("MONETICOPAIEMENT_URLPAYMENT", "paiement.cgi");
?>
