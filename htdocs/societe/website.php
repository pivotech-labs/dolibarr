<?php
/* Copyright (C) 2001-2007 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2005      Brice Davoleau       <brice.davoleau@gmail.com>
 * Copyright (C) 2005-2012 Regis Houssin        <regis.houssin@capnetworks.com>
 * Copyright (C) 2006-2015 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2007      Patrick Raguin  		<patrick.raguin@gmail.com>
 * Copyright (C) 2010      Juanjo Menent        <jmenent@2byte.es>
 * Copyright (C) 2015      Marcos García        <marcosgdf@gmail.com>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 *  \file       htdocs/societe/website.php
 *  \ingroup    societe
 *  \brief      Page of web sites accounts
 */

require '../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/contact/class/contact.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/functions2.lib.php';

$langs->load("companies");

$search_status=GETPOST('search_status');

// Security check
$id = GETPOST('id','int');
if ($user->societe_id) $socid=$user->societe_id;
$result = restrictedArea($user, 'societe', $socid, '&societe');

$limit = GETPOST('limit','int')?GETPOST('limit','int'):$conf->liste_limit;
$sortfield = GETPOST("sortfield",'alpha');
$sortorder = GETPOST("sortorder",'alpha');
$page = GETPOST("page",'int');
if (empty($page) || $page == -1) { $page = 0; }     // If $page is not defined, or '' or -1
$offset = $limit * $page;
$pageprev = $page - 1;
$pagenext = $page + 1;
if (! $sortfield) $sortfield='a.login';
if (! $sortorder) $sortorder='ASC';

// Initialize technical object to manage hooks of page. Note that conf->hooks_modules contains array of hook context
$hookmanager->initHooks(array('websitethirdparty'));


/*
 *	Actions
 */

$parameters=array('id'=>$socid);
$reshook=$hookmanager->executeHooks('doActions',$parameters,$object,$action);    // Note that $action and $object may have been modified by some hooks
if ($reshook < 0) setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');

if (empty($reshook))
{
    // Cancel
    if (GETPOST('cancel','alpha') && ! empty($backtopage))
    {
        header("Location: ".$backtopage);
        exit;
    }

    // Purge search criteria
    if (GETPOST('button_removefilter_x','alpha') || GETPOST('button_removefilter.x','alpha') || GETPOST('button_removefilter','alpha')) // All tests are required to be compatible with all browsers
    {
        $actioncode='';
        $search_agenda_label='';
    }
}



/*
 *	View
 */

$contactstatic = new Contact($db);

$form = new Form($db);

if ($id > 0)
{
	require_once DOL_DOCUMENT_ROOT.'/core/lib/company.lib.php';
	require_once DOL_DOCUMENT_ROOT.'/societe/class/societe.class.php';

	$langs->load("companies");


	$object = new Societe($db);
	$result = $object->fetch($id);

	$title=$langs->trans("WebisteAccounts");
	llxHeader('',$title);

	$head = societe_prepare_head($object);

	dol_fiche_head($head, 'websites', $langs->trans("ThirdParty"), -1, 'company');

    $linkback = '<a href="'.DOL_URL_ROOT.'/societe/list.php?restore_lastsearch_values=1">'.$langs->trans("BackToList").'</a>';

    dol_banner_tab($object, 'socid', $linkback, ($user->societe_id?0:1), 'rowid', 'nom');

    print '<div class="fichecenter">';

    print '<div class="underbanner clearboth"></div>';

	print '<table class="border centpercent">';

    // Prefix
    if (! empty($conf->global->SOCIETE_USEPREFIX))  // Old not used prefix field
    {
    	print '<tr><td class="titlefield">'.$langs->trans('Prefix').'</td><td colspan="3">'.$object->prefix_comm.'</td></tr>';
    }

    if ($object->client)
    {
    	print '<tr><td class="titlefield">';
    	print $langs->trans('CustomerCode').'</td><td colspan="3">';
    	print $object->code_client;
    	if ($object->check_codeclient() <> 0) print ' <font class="error">('.$langs->trans("WrongCustomerCode").')</font>';
    	print '</td></tr>';
    }

    if ($object->fournisseur)
    {
    	print '<tr><td class="titlefield">';
    	print $langs->trans('SupplierCode').'</td><td colspan="3">';
    	print $object->code_fournisseur;
    	if ($object->check_codefournisseur() <> 0) print ' <font class="error">('.$langs->trans("WrongSupplierCode").')</font>';
    	print '</td></tr>';
    }

    print '</table>';

	print '</div>';

	dol_fiche_end();



	$morehtmlcenter='';
	if (! empty($conf->website->enabled))
	{
		if (! empty($user->rights->societe->lire))
		{
			$morehtmlcenter.='<a class="butAction" href="'.$_SERVER['PHP_SELF'].'?action=create'.$out.'">'.$langs->trans("AddWebsiteAccount").'</a>';
		}
		else
		{
			$morehtmlcenter.='<a class="butActionRefused" href="#">'.$langs->trans("AddAction").'</a>';
		}
	}

   	print '<br>';

    $param='&id='.$id;
    if (! empty($contextpage) && $contextpage != $_SERVER["PHP_SELF"]) $param.='&contextpage='.$contextpage;
    if ($limit > 0 && $limit != $conf->liste_limit) $param.='&limit='.$limit;

	print_barre_liste($langs->trans("WebsiteAccounts"), 0, $_SERVER["PHP_SELF"], '', $sortfield, $sortorder, $morehtmlcenter, 0, -1, '', '', '', '', 0, 1, 1);






}


llxFooter();

$db->close();