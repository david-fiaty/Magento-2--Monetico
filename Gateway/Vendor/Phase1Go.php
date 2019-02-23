<?php
/*****************************************************************************
 *
 * "Open source" kit for Monetico Paiement (TM)
 *
 * File "Phase1Go.php":
 *
 * Author   : Euro-Information/e-Commerce
 * Version  : 4.0
 * Date      : 05/06/2014
 *
 * Copyright: (c) 2014 Euro-Information. All rights reserved.
 * License  : see attached document "License.txt".
 *
 *****************************************************************************/

// =============================================================================================================================================================
// SECTION INCLUDE :  Cette section ne doit pas être modifié.
// Attention !! MoneticoPaiement_Config contient la clé, vous devez protéger ce fichier avec tous les mécanismes disponibles dans votre environnement de développement.
// Vous pouvez pour l'instance mettre ce fichier dans un autre répertoire et/ou changer son nom. Dans ce cas, n'oubliez pas d'adapter le chemin d'inclusion ci-dessous.
//
// INCLUDE SECTION :  This section must not be modified
// Warning !! MoneticoPaiement_Config contains the key, you have to protect this file with all the mechanism available in your development environment.
// You may for instance put this file in another directory and/or change its name. If so, don't forget to adapt the include path below.
// =============================================================================================================================================================

require_once("MoneticoPaiement_Config.php");

// PHP implementation of RFC2104 hmac sha1 ---
require_once("MoneticoPaiement_Ept.inc.php");

// =============================================================================================================================================================
// FIN SECTION INCLUDE
//
// END INCLUDE SECTION
// =============================================================================================================================================================

// =============================================================================================================================================================
// SECTION PAIEMENT : Cette section doit être modifiée
// Ci-après, vous trouverez un exemple des valeurs requises pour effectuer un paiement en utilisant la solution Monetico Paiement.
// L'ordre des variables et le format des valeurs doivent correspondre aux spécifications techniques.
//
// PAYMENT SECTION :  This section must be modified 
// Here after, you will find an example of the values needed to demand a payment using the Monetico paiement solution.
// The order of the variables and the format of the values must follow the technical specification. 
// =============================================================================================================================================================
// -------------------------------------------------------------------------------------------------------------------------------------------------------------
// SECTION PAIEMENT - Section générale
// 
// PAYMENT SECTION - General section
// -------------------------------------------------------------------------------------------------------------------------------------------------------------

// Reference: unique, alphaNum (A-Z a-z 0-9), 12 characters max
$sReference = "ref" . date("His");

// Amount : format  "xxxxx.yy" (no spaces)
$sMontant = 1.01;

// Currency : ISO 4217 compliant
$sDevise  = "EUR";

// free texte : a bigger reference, session context for the return on the merchant website
$sTexteLibre = "Texte Libre";

// transaction date : format d/m/y:h:m:s
$sDate = date("d/m/Y:H:i:s");

// Language of the company code
$sLangue = "FR";

// customer email
$sEmail = "test@test.zz";

$sOptions = "";

// -------------------------------------------------------------------------------------------------------------------------------------------------------------
// SECTION PAIEMENT FRACTIONNE - Section spécifique au paiement fractionné
// 
// INSTALMENT PAYMENT SECTION - Section specific to the installment payment
// -------------------------------------------------------------------------------------------------------------------------------------------------------------

// between 2 and 4
// entre 2 et 4
//$sNbrEch = "4";
$sNbrEch = "";

// date echeance 1 - format dd/mm/yyyy
//$sDateEcheance1 = date("d/m/Y");
$sDateEcheance1 = "";

// montant �ch�ance 1 - format  "xxxxx.yy" (no spaces)
//$sMontantEcheance1 = "0.26" . $sDevise;
$sMontantEcheance1 = "";

// date echeance 2 - format dd/mm/yyyy
$sDateEcheance2 = "";

// montant �ch�ance 2 - format  "xxxxx.yy" (no spaces)
//$sMontantEcheance2 = "0.25" . $sDevise;
$sMontantEcheance2 = "";

// date echeance 3 - format dd/mm/yyyy
$sDateEcheance3 = "";

// montant �ch�ance 3 - format  "xxxxx.yy" (no spaces)
//$sMontantEcheance3 = "0.25" . $sDevise;
$sMontantEcheance3 = "";

// date echeance 4 - format dd/mm/yyyy
$sDateEcheance4 = "";

// montant �ch�ance 4 - format  "xxxxx.yy" (no spaces)
//$sMontantEcheance4 = "0.25" . $sDevise;
$sMontantEcheance4 = "";

// =============================================================================================================================================================
// FIN SECTION PAIEMENT
//
// END PAYMENT SECTION 
// =============================================================================================================================================================

// =============================================================================================================================================================
// SECTION CODE : Cette section ne doit pas être modifiée
// 
// CODE SECTION : This section must not be modified
// =============================================================================================================================================================

$oEpt = new MoneticoPaiement_Ept($sLangue);     		
$oHmac = new MoneticoPaiement_Hmac($oEpt);      	        

// Control String for support
$CtlHmac = sprintf(MONETICOPAIEMENT_CTLHMAC, $oEpt->sVersion, $oEpt->sNumero, $oHmac->computeHmac(sprintf(MONETICOPAIEMENT_CTLHMACSTR, $oEpt->sVersion, $oEpt->sNumero)));

// Data to certify
$phase1go_fields = sprintf(MONETICOPAIEMENT_PHASE1GO_FIELDS,     $oEpt->sNumero,
                                              $sDate,
                                              $sMontant,
                                              $sDevise,
                                              $sReference,
                                              $sTexteLibre,
                                              $oEpt->sVersion,
                                              $oEpt->sLangue,
                                              $oEpt->sCodeSociete, 
                                              $sEmail,
                                              $sNbrEch,
                                              $sDateEcheance1,
                                              $sMontantEcheance1,
                                              $sDateEcheance2,
                                              $sMontantEcheance2,
                                              $sDateEcheance3,
                                              $sMontantEcheance3,
                                              $sDateEcheance4,
                                              $sMontantEcheance4,
                                              $sOptions);

// MAC computation
$sMAC = $oHmac->computeHmac($phase1go_fields);

// =============================================================================================================================================================
// FIN SECTION CODE
//
// END CODE SECTION 
// =============================================================================================================================================================
?>

<!--============================================================================================================================================================
  SECTION PAGE HTML
  Ci-dessous se trouve un exemple de code html comprenant le formulaire de paiement. La partie importante est la section FORMULAIRE. Le reste de la section
  PAGE HTML est uniquement là pour mettre en forme l'exemple.
  
  HTML PAGE SECTION
  Here after is an example of html code including the payment form. The important part is the FORM section. The rest of the HTML PAGE section is only here 
  for the example.
===========================================================================================================================================================-->

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
<head>
<meta http-equiv="content-type" content="text/html; charset=ISO-8859-1" />
<meta http-equiv="cache-control" content="no-store, no-cache, must-revalidate, post-check=0, pre-check=0" />
<meta http-equiv="Expires" content="Mon, 26 Jul 1997 05:00:00 GMT" />
<meta http-equiv="pragma" content="no-cache" />
<title>Connexion au serveur de paiement</title>
<link type="text/css" rel="stylesheet" href="moneticopaiement.css" />
</head>

<body>
<div id="header">
        <a href="http://www.monetico.ca"><img src="logomoneticopaiement.png" alt="Monetico Paiement" title="Monetico Paiement" /></a>
</div>
<h1>Connexion au serveur de paiement / <span class="anglais">Connection to the payment server</span></h1>
<div id="presentation">
	<p>
	Cette page g&eacute;n&egrave;re le formulaire de paiement avec des donn&eacute;es arbitraires.<br />
	<span class="anglais">This page generates the payment form with some arbitrary data.</span>
	</p>
</div>

<div id="frm">
<p>
    	Cliquez sur le bouton ci-dessous pour vous connecter au serveur de paiement.<br />
	<span class="anglais">Click on the following button to be redirected to the payment server.</span>
</p>
<!--============================================================================================================================================================
  SECTION FORMULAIRE : cette section doit être copiée dans votre propre page afin d'afficher le bouton de paiement. Aucune modification n'est requise.
  
  FORM SECTION : this section must be copied into your own page in order to display payment button. No modification are needed.
=============================================================================================================================================================-->
<form action="<?php echo $oEpt->sUrlPaiement;?>" method="post" id="PaymentRequest">
<p>
	<input type="hidden" name="version"             id="version"        value="<?php echo $oEpt->sVersion;?>" />
	<input type="hidden" name="TPE"                 id="TPE"            value="<?php echo $oEpt->sNumero;?>" />
	<input type="hidden" name="date"                id="date"           value="<?php echo $sDate;?>" />
	<input type="hidden" name="montant"             id="montant"        value="<?php echo $sMontant . $sDevise;?>" />
	<input type="hidden" name="reference"           id="reference"      value="<?php echo $sReference;?>" />
	<input type="hidden" name="MAC"                 id="MAC"            value="<?php echo $sMAC;?>" />
	<input type="hidden" name="url_retour"          id="url_retour"     value="<?php echo $oEpt->sUrlKO;?>" />
	<input type="hidden" name="url_retour_ok"       id="url_retour_ok"  value="<?php echo $oEpt->sUrlOK;?>" />
	<input type="hidden" name="url_retour_err"      id="url_retour_err" value="<?php echo $oEpt->sUrlKO;?>" />
	<input type="hidden" name="lgue"                id="lgue"           value="<?php echo $oEpt->sLangue;?>" />
	<input type="hidden" name="societe"             id="societe"        value="<?php echo $oEpt->sCodeSociete;?>" />
	<input type="hidden" name="texte-libre"         id="texte-libre"    value="<?php echo HtmlEncode($sTexteLibre);?>" />
	<input type="hidden" name="mail"                id="mail"           value="<?php echo $sEmail;?>" />
	<!-------------------------------------------------------------------------------------------------------------------------------------------------------------
      SECTION PAIEMENT FRACTIONNE - Section spécifique au paiement fractionné
	  
	  INSTALLMENT PAYMENT SECTION - Section specific to the installment payment
	-------------------------------------------------------------------------------------------------------------------------------------------------------------->
	<input type="hidden" name="nbrech"              id="nbrech"         value="<?php echo $sNbrEch;?>" />
	<input type="hidden" name="dateech1"            id="dateech1"       value="<?php echo $sDateEcheance1;?>" />
	<input type="hidden" name="montantech1"         id="montantech1"    value="<?php echo $sMontantEcheance1;?>" />
	<input type="hidden" name="dateech2"            id="dateech2"       value="<?php echo $sDateEcheance2;?>" />
	<input type="hidden" name="montantech2"         id="montantech2"    value="<?php echo $sMontantEcheance2;?>" />
	<input type="hidden" name="dateech3"            id="dateech3"       value="<?php echo $sDateEcheance3;?>" />
	<input type="hidden" name="montantech3"         id="montantech3"    value="<?php echo $sMontantEcheance3;?>" />
	<input type="hidden" name="dateech4"            id="dateech4"       value="<?php echo $sDateEcheance4;?>" />
	<input type="hidden" name="montantech4"         id="montantech4"    value="<?php echo $sMontantEcheance4;?>" />
	<!-------------------------------------------------------------------------------------------------------------------------------------------------------------
      FIN SECTION PAIEMENT FRACTIONNE
	  
	  END INSTALLMENT PAYMENT SECTION
	-------------------------------------------------------------------------------------------------------------------------------------------------------------->
	<input type="submit" name="bouton"              id="bouton"         value="Connexion / Connection" />
</p>
</form>
<!--============================================================================================================================================================
  FIN SECTION FORMULAIRE
  
  END FORM SECTION
=============================================================================================================================================================-->
</div>
<div id="source">
	<h2>Uniquement pour le d&eacute;bogage / <span class="anglais">For debug purpose only</span></h2>
        <p>
	Code source du formulaire.  <br />
	<span class="anglais">Form source code.</span>
       </p>
<pre>
&lt;form <span class="name">action</span>="<span class="value"><?php echo $oEpt->sUrlPaiement;?>"</span> method="post" id="PaymentRequest"&gt;
&lt;input type="hidden" name="<span class="name">version</span>"          value="<span class="value"><?php echo $oEpt->sVersion;?></span>" /&gt;
&lt;input type="hidden" name="<span class="name">TPE</span>"              value="<span class="value"><?php echo $oEpt->sNumero;?></span>" /&gt;
&lt;input type="hidden" name="<span class="name">date</span>"             value="<span class="value"><?php echo $sDate;?></span>" /&gt;
&lt;input type="hidden" name="<span class="name">montant</span>"          value="<span class="value"><?php echo $sMontant . $sDevise;?></span>" /&gt;
&lt;input type="hidden" name="<span class="name">reference</span>"        value="<span class="value"><?php echo $sReference;?></span>" /&gt;
&lt;input type="hidden" name="<span class="name">MAC</span>"              value="<span class="value"><?php echo $sMAC;?></span>" /&gt;
&lt;input type="hidden" name="<span class="name">url_retour</span>"       value="<span class="value"><?php echo $oEpt->sUrlKO;?></span>" /&gt;
&lt;input type="hidden" name="<span class="name">url_retour_ok</span>"    value="<span class="value"><?php echo $oEpt->sUrlOK;?></span>" /&gt;
&lt;input type="hidden" name="<span class="name">url_retour_err</span>"   value="<span class="value"><?php echo $oEpt->sUrlKO;?></span>" /&gt;
&lt;input type="hidden" name="<span class="name">lgue</span>"             value="<span class="value"><?php echo $oEpt->sLangue;?></span>" /&gt;
&lt;input type="hidden" name="<span class="name">societe</span>"          value="<span class="value"><?php echo $oEpt->sCodeSociete;?></span>" /&gt;
&lt;input type="hidden" name="<span class="name">texte-libre</span>"      value="<span class="value"><?php echo HtmlEncode($sTexteLibre);?></span>" /&gt;
&lt;input type="hidden" name="<span class="name">mail</span>"             value="<span class="value"><?php echo $sEmail;?></span>" /&gt;
&lt;!-- Uniquement pour le Paiement fractionn&eacute; / Only for installment payment --&gt;
&lt;input type="hidden" name="<span class="name">nbrech</span>"           value="<span class="value"><?php echo $sNbrEch;?></span>" /&gt;
&lt;input type="hidden" name="<span class="name">dateech1</span>"         value="<span class="value"><?php echo $sDateEcheance1;?></span>" /&gt;
&lt;input type="hidden" name="<span class="name">montantech1</span>"      value="<span class="value"><?php echo $sMontantEcheance1;?></span>" /&gt;
&lt;input type="hidden" name="<span class="name">dateech2</span>"         value="<span class="value"><?php echo $sDateEcheance2;?></span>" /&gt;
&lt;input type="hidden" name="<span class="name">montantech2</span>"      value="<span class="value"><?php echo $sMontantEcheance2;?></span>" /&gt;
&lt;input type="hidden" name="<span class="name">dateech3</span>"         value="<span class="value"><?php echo $sDateEcheance3;?></span>" /&gt;
&lt;input type="hidden" name="<span class="name">montantech3</span>"      value="<span class="value"><?php echo $sMontantEcheance3;?></span>" /&gt;
&lt;input type="hidden" name="<span class="name">dateech4</span>"         value="<span class="value"><?php echo $sDateEcheance4;?></span>" /&gt;
&lt;input type="hidden" name="<span class="name">montantech4</span>"      value="<span class="value"><?php echo $sMontantEcheance4;?></span>" /&gt;
&lt;!-- --&gt;
&lt;input type="submit" name="<span class="name">bouton</span>"           value="<span class="value">Connexion / Connection</span>" /&gt;
&lt;/form&gt;
</pre>
</div>
<div>
	<p>
	Cha&icirc;ne de contr&ocirc;le &agrave; fournir au support en cas de probl&egrave;mes <br />
	<span class="anglais">Control string needed by support in case of problems</span>
	</p>
	<pre><?php echo $CtlHmac;?></pre>
	<p>
	Cha&icirc;ne utilis&eacute;e pour le calcul du sceau HMAC <br />
	Num&eacute;ro de TPE*date*montant*r&eacute;f&eacute;rence*texte libre*version*code langue*code soci&eacute;t&eacute;*email*nombre &eacute;ch&eacute;ance*date &eacute;ch&eacute;ance1*montant &eacute;ch&eacute;ance1*date &eacute;ch&eacute;ance2*montant &eacute;ch&eacute;ance2*date &eacute;ch&eacute;ance3*montant &eacute;ch&eacute;ance3*date &eacute;ch&eacute;ance4*montant &eacute;ch&eacute;ance4*options<br />
	<span class="anglais">String used to generate the HMAC<br />
	EPT number*date*amount*reference*free text*version*language code*company code*e-mail*installments number*date installment1*amount installment1*date installment2*amount installment2*date installment3*amount installment3*date installment4*amount installment4*options</span>
	</p>
	<pre><?php echo $phase1go_fields;?></pre>
</div>
<div id="footer">
        <p>
	Cette page est fournie comme un exemple d'impl&eacute;mentation de Monetico Paiement.<br />
	Elle n'a pas pour but de r&eacute;pondre &agrave; toutes les configurations existantes. &copy; 2014 Euro Information.<br />
	<span class="anglais">This page is just an example of the use of Monetico Paiement.<br />
	Its main purpose is not to give an answer to every existing configurations. &copy; 2014 Euro Information</span>
	</p>
</div>
</body>
</html>
<!--============================================================================================================================================================
  FIN SECTION PAGE HTML
  
  END HTML PAGE SECTION
=============================================================================================================================================================-->