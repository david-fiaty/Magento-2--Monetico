<?php
/*****************************************************************************
 *
 * "Open source" kit for Monetico Paiement(TM).
 * Process Monetico Paiement. Sample RFC2104 compliant with PHP4 skeleton.
 *
 * File "Phase2Back.php":
 *
 * Author   : Euro-Information/e-Commerce
 * Version  : 4.0
 * Date      : 05/06/2014
 *
 * Copyright: (c) 2014 Euro-Information. All rights reserved.
 * License  : see attached document "Licence.txt".
 *
 *****************************************************************************/

header("Pragma: no-cache");
header("Content-type: text/plain");

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

// --- PHP implementation of RFC2104 hmac sha1 ---
require_once("MoneticoPaiement_Ept.inc.php");

// =============================================================================================================================================================
// FIN SECTION INCLUDE
//
// END INCLUDE SECTION
// =============================================================================================================================================================

// =============================================================================================================================================================
// SECTION CODE : Cette section ne doit pas être modifiée
// 
// CODE SECTION : This section must not be modified
// =============================================================================================================================================================

// Begin Main : Retrieve Variables posted by Monetico Paiement Server
$MoneticoPaiement_bruteVars = getMethode();

// TPE init variables
$oEpt = new MoneticoPaiement_Ept();
$oHmac = new MoneticoPaiement_Hmac($oEpt);

// Message Authentication
$phase2back_fields = sprintf(MONETICOPAIEMENT_PHASE2BACK_FIELDS, $oEpt->sNumero,
                        $MoneticoPaiement_bruteVars["date"],
                        $MoneticoPaiement_bruteVars['montant'],
                        $MoneticoPaiement_bruteVars['reference'],
                        $MoneticoPaiement_bruteVars['texte-libre'],
                        $oEpt->sVersion,
                        $MoneticoPaiement_bruteVars['code-retour'],
                        $MoneticoPaiement_bruteVars['cvx'],
                        $MoneticoPaiement_bruteVars['vld'],
                        $MoneticoPaiement_bruteVars['brand'],
                        $MoneticoPaiement_bruteVars['status3ds'],
                        $MoneticoPaiement_bruteVars['numauto'],
                        $MoneticoPaiement_bruteVars['motifrefus'],
                        $MoneticoPaiement_bruteVars['originecb'],
                        $MoneticoPaiement_bruteVars['bincb'],
                        $MoneticoPaiement_bruteVars['hpancb'],
                        $MoneticoPaiement_bruteVars['ipclient'],
                        $MoneticoPaiement_bruteVars['originetr'],
                        $MoneticoPaiement_bruteVars['veres'],
                        $MoneticoPaiement_bruteVars['pares']
					);

if ($oHmac->computeHmac($phase2back_fields) == strtolower($MoneticoPaiement_bruteVars['MAC']))
	{
// =============================================================================================================================================================
// FIN SECTION CODE
//
// END CODE SECTION 
// =============================================================================================================================================================

// =============================================================================================================================================================
// SECTION IMPLEMENTATION : Vous devez modifier ce code afin d'y mettre votre propre logique métier
// 
// IMPLEMENTATION SECTION : You must adapt this code with your own application logic.
// =============================================================================================================================================================

	switch($MoneticoPaiement_bruteVars['code-retour']) {

		case "Annulation" :
			// Paiement refusé
			// Insérez votre code ici (envoi d'email / mise à jour base de données)
			// Attention : une autorisation peut toujours être délivrée pour ce paiement
			//
			// Payment has been refused
			// put your code here (email sending / Database update)
			// Attention : an authorization may still be delivered for this payment
			break;

		case "payetest":
		    // Paiement accepté sur le serveur de test
			// Insérez votre code ici (envoi d'email / mise à jour base de données)
			//
			// Payment has been accepted on the test server
			// put your code here (email sending / Database update)
			break;

		case "paiement":
			// Paiement accepté sur le serveur de production
			// Insérez votre code ici (envoi d'email / mise à jour base de données)
			//
			// Payment has been accepted on the productive server
			// put your code here (email sending / Database update)
			break;

		
		/*** SEULEMENT POUR LES PAIEMENTS FRACTIONNES ***/
		/***              ONLY FOR MULTIPART PAYMENT              ***/
		case "paiement_pf2":
		case "paiement_pf3":
		case "paiement_pf4":
			// Paiement accepté sur le serveur de production pour l'échéance #N
			// Le code de retour est du type paiement_pf[#N]
			// Insérez votre code ici (envoi d'email / mise à jour base de données)
			// Le montant du paiement pour cette échéance se trouve dans $MoneticoPaiement_bruteVars['montantech']
			//
			// Payment has been accepted on the productive server for the part #N
			// return code is like paiement_pf[#N]
			// put your code here (email sending / Database update)
			// You have the amount of the payment part in $MoneticoPaiement_bruteVars['montantech']
			break;

		case "Annulation_pf2":
		case "Annulation_pf3":
		case "Annulation_pf4":
		    // Paiement refusé sur le serveur de production pour l'échéance #N
			// Le code de retour est du type Annulation_pf[#N]
			// Insérez votre code ici (envoi d'email / mise à jour base de données)
			// Le montant du paiement pour cette échéance se trouve dans $MoneticoPaiement_bruteVars['montantech']
			//
			// Payment has been refused on the productive server for the part #N
			// return code is like Annulation_pf[#N]
			// put your code here (email sending / Database update)
			// You have the amount of the payment part in $MoneticoPaiement_bruteVars['montantech']
			break;
	}

// =============================================================================================================================================================
// FIN SECTION IMPLEMENTATION
// 
// END IMPLEMENTATION SECTION
// =============================================================================================================================================================

// =============================================================================================================================================================
// SECTION CODE 2 : Cette section ne doit pas être modifiée
// 
// CODE SECTION 2 : This section must not be modified
// =============================================================================================================================================================

	$receipt = MONETICOPAIEMENT_PHASE2BACK_MACOK;

}
else
{
	// traitement en cas de HMAC incorrect
	// your code if the HMAC doesn't match
	$receipt = MONETICOPAIEMENT_PHASE2BACK_MACNOTOK.$phase2back_fields;
}

// =============================================================================================================================================================
// FIN SECTION CODE 2
//
// END CODE SECTION 2
// =============================================================================================================================================================

//-----------------------------------------------------------------------------
// Send receipt to Monetico Paiement server
//-----------------------------------------------------------------------------
printf (MONETICOPAIEMENT_PHASE2BACK_RECEIPT, $receipt);

// Copyright (c) 2014 Euro-Information
// All rights reserved. ---
?>
