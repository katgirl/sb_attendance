<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2013 Leo Feyer
 *
 * @package   Attendance
 * @author    Sebastian Buck
 * @license   LGPL
 * @copyright Sebastian Buck 2013
 */


/**
 * Übersetzungen für die Eingabemaske der Anwesenheitsliste unter Themes/Frontend-Module
 */
 
// Legends
$GLOBALS['TL_LANG']['tl_module']['attendance_calender_legend'] = 'Kalender-Auswahl';
$GLOBALS['TL_LANG']['tl_module']['attendance_show_legend'] = 'Abgelaufene Termine und Sperrzeiten';
$GLOBALS['TL_LANG']['tl_module']['attendance_statusOptions_legend'] = 'Statusoptionen';
$GLOBALS['TL_LANG']['tl_module']['attendance_memberRoles_legend'] = 'Mitglieder markieren';
$GLOBALS['TL_LANG']['tl_module']['attendance_style_legend'] = 'Darstellungsoptionen';

// Felder
$GLOBALS['TL_LANG']['tl_module']['al_pickCalendar'][0] = 'Kalenderauswahl';
$GLOBALS['TL_LANG']['tl_module']['al_pickCalendar'][1] = 'Wählen Sie einen oder mehrere Kalender aus.';

$GLOBALS['TL_LANG']['tl_module']['al_expiredEvents'][0] = 'Abgelaufene Termine';
$GLOBALS['TL_LANG']['tl_module']['al_expiredEvents'][1] = 'Wie viele abgelaufene Termine sollen angezeigt werden?';

$GLOBALS['TL_LANG']['tl_module']['al_expireTime'][0] = 'Sperrzeit von Terminen';
$GLOBALS['TL_LANG']['tl_module']['al_expireTime'][1] = 'Legen Sie die Sperrzeit (in Stunden) fest, ab wann die Statusänderungen vor einem Termin gesperrt werden sollen.';

$GLOBALS['TL_LANG']['tl_module']['al_disableThird'][0] = 'Dritte Option deaktivieren';
$GLOBALS['TL_LANG']['tl_module']['al_disableThird'][1] = 'Die Dritte Statusoption "komme später" wird hierüber de/aktiviert.';

$GLOBALS['TL_LANG']['tl_module']['al_defaultStatus'][0] = 'Standard Status';
$GLOBALS['TL_LANG']['tl_module']['al_defaultStatus'][1] = 'Dieser Status wird für neue Felder verwendet.';

$GLOBALS['TL_LANG']['tl_module']['al_roleAdvice'][0] = ' ';
$GLOBALS['TL_LANG']['tl_module']['al_roleAdvice'][1] = 'Hinweis: Die Vergabe von Mitgliederrollen kann in der Mitgliederverwaltung vorgenommen werden.';

$GLOBALS['TL_LANG']['tl_module']['al_name'][0] = 'Darstellung des Namen';
$GLOBALS['TL_LANG']['tl_module']['al_name'][1] = 'Wie soll der Name eines Mitgliedes angezeigt werden?';

$GLOBALS['TL_LANG']['tl_module']['al_iconSet'][0] = 'Icon-Set';
$GLOBALS['TL_LANG']['tl_module']['al_iconSet'][1] = 'Hier können Sie das verwendete Icon-Set auswählen.';

$GLOBALS['TL_LANG']['tl_module']['al_useCSS'][0] = 'Mitgeliefertes CSS verwenden';
$GLOBALS['TL_LANG']['tl_module']['al_useCSS'][1] = 'Soll das Aussehen der Anwesenheitsliste durch das mitgelieferte CSS beeinflusst werden?';

// Optionen im Select-Feld (Icon-Set)
$GLOBALS['TL_LANG']['tl_module']['flat_thick'] = 'Flat-Design, breite Symbole';
$GLOBALS['TL_LANG']['tl_module']['flat_thick_alternative'] = 'Flat-Design, breite, alternative Symbole ';
$GLOBALS['TL_LANG']['tl_module']['flat_thin'] = 'Flat-Design dünne Symbole';

// Optionen im Radio-Feld (Standard-Status)
$GLOBALS['TL_LANG']['tl_module']['al_radio']['0'] = 'Unbekannt';
$GLOBALS['TL_LANG']['tl_module']['al_radio']['1'] = 'Anwesend';
$GLOBALS['TL_LANG']['tl_module']['al_radio']['2'] = 'Abwesend';
$GLOBALS['TL_LANG']['tl_module']['al_radio']['3'] = 'Komme später';

// Optionen der Darstellung des Namen
$GLOBALS['TL_LANG']['tl_module']['al_name']['username'] = 'Nutzername';
$GLOBALS['TL_LANG']['tl_module']['al_name']['firstname'] = 'Vorname';
$GLOBALS['TL_LANG']['tl_module']['al_name']['lastname'] = 'Nachname';
$GLOBALS['TL_LANG']['tl_module']['al_name']['first_last'] = 'Vor- und Nachname';
