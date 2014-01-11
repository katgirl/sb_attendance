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
 * Class ModuleAttendanceList
 *
 * @copyright  Sebastian Buck 2013
 * @author     Sebastian Buck
 * @package    Attendance
 */
class ModuleAttendanceList extends \Module
{
	/**
	 * Template
	 * @var string
	 *
	 * Das zugehörige Template (Ausgabe im Frontend) wird festgelegt
	 *
	 */
	protected $strTemplate = 'mod_attendance_list';

	/**
	 * Generate the module
	 */
	protected function compile()
	{	
		/**
		 * Statusänderung
		 */
			// Überprüfen, ob POST-Variablen gesetzt sind, also ein Status geändert werden sollte
			if ($this->Input->post('m_id'))
			{
				// Variablen mit POST-Werten belegen
				$POST_m_id = $this->Input->post('m_id');
				$POST_e_id = $this->Input->post('e_id');
				$POST_status = $this->Input->post('status');
				
				// Statusänderung
				switch ($POST_status) 
					{
						case 0:
							$POST_status = 1;							
							break;
						case 1:
							$POST_status = 2;							
							break;
						case 2:
							$POST_status = 3;							
							break;
						case 3:
							$POST_status = 1;							
							break;
					}
				
				// akteulle Uhrzeit als Zeitstempel
				$time = time();
				
				// Eintragen in DB
				$changeStatus = Database::getInstance()
						->prepare('UPDATE tl_attendance SET attendance=?,tstamp=? WHERE m_id=? AND e_id=?')
						->execute($POST_status,$time,$POST_m_id,$POST_e_id);			
			}
		
		
		/**
		 * Daten aus Datenbank laden
		 */
		 
			// aktive SpielerIDs aus tl_attendance holen		
			$result = Database::getInstance()->prepare('SELECT DISTINCT t1.username, t1.id FROM tl_member t1 JOIN tl_attendance t2 ON (t2.m_id = t1.id) ORDER BY t1.username')->execute();	
			$spieler = $result->fetchAllAssoc();

			// aktive TerminIDs aus tl_attendance holen
			$result = Database::getInstance()->prepare('SELECT DISTINCT t1.id,t1.title,t1.startDate,t1.startTime FROM tl_calendar_events t1 JOIN tl_attendance t2 ON (t2.e_id = t1.id) ORDER BY t1.startTime')->execute();		
			$termine = $result->fetchAllAssoc();

			// ausgewähltes IconSet laden
			$result = Database::getInstance()->prepare('SELECT al_iconSet FROM tl_module WHERE type=?')->limit(1)->execute(attendance_list);		
			$iconSet = $result->al_iconSet;		
			$iconSet = $iconSet .'_icon_set';

			// optionales CSS - Daten laden
			$result = Database::getInstance()->prepare('SELECT al_useCSS FROM tl_module WHERE type=?')->limit(1)->execute(attendance_list);
			$useCSS = $result->al_useCSS;
			
			// Trainer suchen
			$result = Database::getInstance()->prepare('SELECT id FROM tl_member WHERE al_coachRole=?')->execute(1);		
			$coach = $result->id;
			
			// Admin suchen
			$result = Database::getInstance()->prepare('SELECT id FROM tl_member WHERE al_adminRole=?')->execute(1);		
			$admin = $result->id;
			
			// Sperrzeit laden
			$result = Database::getInstance()->prepare('SELECT al_expireTime FROM tl_module WHERE type=?')->limit(1)->execute(attendance_list);		
			$expireTime = $result->al_expireTime;
			$expireTime = $expireTime * 3600;
			
			// Anzahl abgelaufener Termine
			$result = Database::getInstance()->prepare('SELECT al_expiredEvents FROM tl_module WHERE type=?')->limit(1)->execute(attendance_list);		
			$expiredEvents = $result->al_expiredEvents;
			
		/**
		 * ENDE: Daten aus Datenbank laden
		 */
		 
		 
		/**
		 * Eingeloggten Nutzer Laden
		 */
			$this->import('FrontendUser');
			$logged_user = $this->FrontendUser->id;
			
			// Abfangen von Bearbeitungsmöglichkeit bei keinem eingeloggten Nutzer
			if(!$logged_user)
			{
				$logged_user = 'kein eingelogter Nutzer';
			}
		
			
		/**
		 * CSS einbinden
		 */
		 
			// Einbinden der mitgelieferten CSS-Anweisungen, wenn die Option gesetzt wurde
			if($useCSS==1)
			{
				if (TL_MODE == 'FE')
				{
					$GLOBALS['TL_CSS']['al_css'] = 'system/modules/sb_attendance/assets/css/al_style.css';
				}
			}
		
		/**
		 * Daten verarbeiten
		 */
		 
			/**
			 * Zukünftige und abgelaufene Events trennen
			 */
				$future = array();
				$expired = array();
				
				foreach ($termine as $termin)
				{
					if (($termin['startTime']-$expireTime) < time())
					{
						array_push($expired, $termin);
					}
					else
					{
						array_push($future, $termin);
					}
				}	
			
				// Fehler abfangen, wenn Anzahl anzuzeigener, abgelaufener Events 
				// größer ist, als die Anzahl der abgelaufenen Events
				if($expiredEvents>sizeof($expired))
				{
					$expiredEvents = sizeof($expired);
				}			
			
				// Anzahl der abgelaufenen Events zu dem Array der kommenden Events hinzufügen
				$i = 1;			
				while ($i<=$expiredEvents)
				{	
					$last = array_pop($expired);				
					array_unshift($future, $last);
					
					$i++;
				}			
				$termine = $future;			
		 
			/**
			 * Spaltenüberschriften bilden
			 */
			 
				// Überschriften-Array definieren
				$headings = array();
				
				$g=1;				
				// Überschriften-Array mit den Titeln der Events befüllen
				foreach ($termine as $termin)
				{					
					// Abgelaufene Events 
					if($g<=$expiredEvents)
					{
						$termin['title'] = "<td class='expired'><p class='al_title'>".$termin['title']."</p>";
					} 
					else if($g>$expiredEvents)
					{
						$termin['title'] = "<td><p class='al_title'>".$termin['title']."</p>";
					}
				
					// Datumsformate berücksichtigen (Umwandeln in menschenlesbar)
					if ($termin['startTime']!=$termin['startDate'])
					{
						$termin['startTime'] = "<br>".date($GLOBALS['TL_CONFIG']['timeFormat'],$termin['startTime']).$GLOBALS['TL_LANG']['al_frontend']['time']."</p>";
					}
					else
					{
						$termin['startTime'] = '</p>';
					}	
					
					$termin['startDate'] = "<p class='al_date'>".date($GLOBALS['TL_CONFIG']['dateFormat'],$termin['startDate']);
					
					
					// aktive Spieleranzahl holen (abhängig ob ein Trainer definiert ist oder nicht)					
					if ($coach)
					{
						$result = Database::getInstance()
							->prepare('SELECT id FROM tl_attendance WHERE e_id=? AND (attendance=? OR attendance=?) AND m_id!=?')
							->execute($termin['id'],1,3,$coach);
					}
					else
					{
						$result = Database::getInstance()
							->prepare('SELECT id FROM tl_attendance WHERE e_id=? AND (attendance=? OR attendance=?)')
							->execute($termin['id'],1,3);
					}		
							
					$number = "<p>".$GLOBALS['TL_LANG']['al_frontend']['attendants'].":<br>".$result->count()."</p></td>";

					$termin['summe']=$number;
					
					// Überschriften-Array erzeugen
					array_push($headings, $termin);
					
					$g++;			
				}
				
			
			/**
			 * Zeilen erzeugen
			 */

			// Trainer an erste Stelle im Array sortieren
			$t=1;
			foreach ($spieler as $trainer)
			{
				if($trainer['id']==$coach)
				{
					array_unshift($spieler, $trainer);
					unset($spieler[$t]);
				}
				$t++;
			}
			
			// Pro Spieler eine Reihe erzeugen
			foreach ($spieler as $reihe)
			{				
				// Variablen definieren 
				$name = $reihe['username'];
				$stati = array ();
				
				$j=1;
				// abgelaufene Termine für Array aufbereiten und übergeben
				foreach ($termine as $termin)
				{			
					// Abgelaufene Events 
					if($j<=$expiredEvents)
					{
						// $stati Datensatz erstellen
						// Pro Termin den entsprechenden Anwesenheitsstatus laden 
						$result = Database::getInstance()->prepare('SELECT attendance,tstamp FROM tl_attendance WHERE m_id=? AND e_id=?')->execute($reihe['id'],$termin['id']);	
						$attendances = $result->fetchAllAssoc();
						
						// attendance-Wert auslesen und entsprechenden Bildpfad auf Array speichern	
						foreach ($attendances as $attendance)
						{					
							$att = $attendance['attendance'];						
							
							// Abhängig von Nutzerrolle die Felder editierbar machen oder nur als Bilder ausgeben
							if($logged_user==$coach || $logged_user==$admin)
							{						
								switch ($att) 
								{
									case 0:
										$att = 
											'<td class="expired"><form action="#" method="POST">
												<input type="hidden" name="REQUEST_TOKEN" value="{{request_token}}">
												<input 
													type="image" 
													src="system/modules/sb_attendance/assets/img/'.$iconSet.'/unknown_expired.png"
													alt="'.ucfirst($reihe['username']).': '.$GLOBALS['TL_LANG']['al_frontend']['unknown'].'" 
													title="'.ucfirst($reihe['username']).': '.$GLOBALS['TL_LANG']['al_frontend']['unknown'].'"
												>
												<input type="hidden" value="0" name="status">											
												<input type="hidden" value="'.$reihe['id'].'" name="m_id">
												<input type="hidden" value="'.$termin['id'].'" name="e_id">
											</form></td>';
										break;
									case 1:
										$att = 
											'<td class="expired"><form action="#" method="POST">
												<input type="hidden" name="REQUEST_TOKEN" value="{{request_token}}">
												<input 
													type="image" 
													src="system/modules/sb_attendance/assets/img/'.$iconSet.'/yes_expired.png"
													alt="'.ucfirst($reihe['username']).': '.$GLOBALS['TL_LANG']['al_frontend']['yes']
															.' - '.date($GLOBALS['TL_CONFIG']['datimFormat'],$attendance['tstamp']).$GLOBALS['TL_LANG']['al_frontend']['time'].'"
													title="'.ucfirst($reihe['username']).': '.$GLOBALS['TL_LANG']['al_frontend']['yes']
															.' - '.date($GLOBALS['TL_CONFIG']['datimFormat'],$attendance['tstamp']).$GLOBALS['TL_LANG']['al_frontend']['time'].'"
												>
												<input type="hidden" value="1" name="status">
												<input type="hidden" value="'.$reihe['id'].'" name="m_id">
												<input type="hidden" value="'.$termin['id'].'" name="e_id">
											</form></td>';
										break;
									case 2:
										$att = 
											'<td class="expired"><form action="#" method="POST">
												<input type="hidden" name="REQUEST_TOKEN" value="{{request_token}}">
												<input 
													type="image" 
													src="system/modules/sb_attendance/assets/img/'.$iconSet.'/no_expired.png"
													alt="'.ucfirst($reihe['username']).': '.$GLOBALS['TL_LANG']['al_frontend']['no']
															.' - '.date($GLOBALS['TL_CONFIG']['datimFormat'],$attendance['tstamp']).$GLOBALS['TL_LANG']['al_frontend']['time'].'"
													title="'.ucfirst($reihe['username']).': '.$GLOBALS['TL_LANG']['al_frontend']['no']
															.' - '.date($GLOBALS['TL_CONFIG']['datimFormat'],$attendance['tstamp']).$GLOBALS['TL_LANG']['al_frontend']['time'].'"
												>
												<input type="hidden" value="2" name="status">
												<input type="hidden" value="'.$reihe['id'].'" name="m_id">
												<input type="hidden" value="'.$termin['id'].'" name="e_id">
											</form></td>';
										break;
									case 3:
										$att = 
											'<td class="expired"><form action="#" method="POST">
												<input type="hidden" name="REQUEST_TOKEN" value="{{request_token}}">
												<input 
													type="image" 
													src="system/modules/sb_attendance/assets/img/'.$iconSet.'/later_expired.png"
													alt="'.ucfirst($reihe['username']).': '.$GLOBALS['TL_LANG']['al_frontend']['later']
															.' - '.date($GLOBALS['TL_CONFIG']['datimFormat'],$attendance['tstamp']).$GLOBALS['TL_LANG']['al_frontend']['time'].'"
													title="'.ucfirst($reihe['username']).': '.$GLOBALS['TL_LANG']['al_frontend']['later']
															.' - '.date($GLOBALS['TL_CONFIG']['datimFormat'],$attendance['tstamp']).$GLOBALS['TL_LANG']['al_frontend']['time'].'"
												>
												<input type="hidden" value="3" name="status">
												<input type="hidden" value="'.$reihe['id'].'" name="m_id">
												<input type="hidden" value="'.$termin['id'].'" name="e_id">
											</form></td>';
										break;
								}					
							}						
							else
							{
								switch ($att) 
								{
									case 0:
										$att = '<td class="expired"><img src="system/modules/sb_attendance/assets/img/'.$iconSet.'/unknown_expired.png" 
													alt="'.ucfirst($reihe['username']).': '.$GLOBALS['TL_LANG']['al_frontend']['unknown'].'" 
													title="'.ucfirst($reihe['username']).': '.$GLOBALS['TL_LANG']['al_frontend']['unknown'].'"></td>';
										break;
									case 1:
										$att = '<td class="expired"><img src="system/modules/sb_attendance/assets/img/'.$iconSet.'/yes_expired.png" 
													alt="'.ucfirst($reihe['username']).': '.$GLOBALS['TL_LANG']['al_frontend']['yes']
															.' - '.date($GLOBALS['TL_CONFIG']['datimFormat'],$attendance['tstamp']).$GLOBALS['TL_LANG']['al_frontend']['time'].'"
													title="'.ucfirst($reihe['username']).': '.$GLOBALS['TL_LANG']['al_frontend']['yes']
															.' - '.date($GLOBALS['TL_CONFIG']['datimFormat'],$attendance['tstamp']).$GLOBALS['TL_LANG']['al_frontend']['time'].'"></td>';
										break;
									case 2:
										$att = '<td class="expired"><img src="system/modules/sb_attendance/assets/img/'.$iconSet.'/no_expired.png" 
													alt="'.ucfirst($reihe['username']).': '.$GLOBALS['TL_LANG']['al_frontend']['no']
															.' - '.date($GLOBALS['TL_CONFIG']['datimFormat'],$attendance['tstamp']).' Uhr"
													title="'.ucfirst($reihe['username']).': '.$GLOBALS['TL_LANG']['al_frontend']['no']
															.' - '.date($GLOBALS['TL_CONFIG']['datimFormat'],$attendance['tstamp']).' Uhr"></td>';
										break;
									case 3:
										$att = '<td class="expired"><img src="system/modules/sb_attendance/assets/img/'.$iconSet.'/later_expired.png" 
													alt="'.ucfirst($reihe['username']).': '.$GLOBALS['TL_LANG']['al_frontend']['later']
															.' - '.date($GLOBALS['TL_CONFIG']['datimFormat'],$attendance['tstamp']).$GLOBALS['TL_LANG']['al_frontend']['time'].'"
													title="'.ucfirst($reihe['username']).': '.$GLOBALS['TL_LANG']['al_frontend']['later']
															.' - '.date($GLOBALS['TL_CONFIG']['datimFormat'],$attendance['tstamp']).$GLOBALS['TL_LANG']['al_frontend']['time'].'"></td>';
										break;
								}					
							}
							
								
							array_push($stati, $att);
						}
						$j++;
					}
				}
				
				// kommende Termine für Array aufbereiten und übergeben
				$k=1;
				foreach ($termine as $termin)
				{						
					if($k>$expiredEvents)
					{					
						// Pro Termin den entsprechenden Anwesenheitsstatus laden 
						$result = Database::getInstance()->prepare('SELECT attendance,tstamp FROM tl_attendance WHERE m_id=? AND e_id=?')->execute($reihe['id'],$termin['id']);	
						$attendances = $result->fetchAllAssoc();
						
						// attendance-Wert auslesen und entsprechenden Bildpfad auf Array speichern	
						foreach ($attendances as $attendance)
						{					
							$att = $attendance['attendance'];						
							
							// Abhängig von Nutzerrolle die Felder editierbar machen oder nur als Bilder ausgeben
							if($logged_user==$reihe['id'] || $logged_user==$coach || $logged_user==$admin)
							{						
								switch ($att) 
								{
									case 0:
										$att = 
											'<td><form action="#" method="POST">
												<input type="hidden" name="REQUEST_TOKEN" value="{{request_token}}">
												<input 
													type="image" 
													src="system/modules/sb_attendance/assets/img/'.$iconSet.'/unknown.png"
													alt="'.ucfirst($reihe['username']).': '.$GLOBALS['TL_LANG']['al_frontend']['unknown'].'" 
													title="'.ucfirst($reihe['username']).': '.$GLOBALS['TL_LANG']['al_frontend']['unknown'].'"
												>
												<input type="hidden" value="0" name="status">											
												<input type="hidden" value="'.$reihe['id'].'" name="m_id">
												<input type="hidden" value="'.$termin['id'].'" name="e_id">
											</form></td>';
										break;
									case 1:
										$att = 
											'<td><form action="#" method="POST">
												<input type="hidden" name="REQUEST_TOKEN" value="{{request_token}}">
												<input 
													type="image" 
													src="system/modules/sb_attendance/assets/img/'.$iconSet.'/yes.png"
													alt="'.ucfirst($reihe['username']).': '.$GLOBALS['TL_LANG']['al_frontend']['yes']
															.' - '.date($GLOBALS['TL_CONFIG']['datimFormat'],$attendance['tstamp']).$GLOBALS['TL_LANG']['al_frontend']['time'].'"
													title="'.ucfirst($reihe['username']).': '.$GLOBALS['TL_LANG']['al_frontend']['yes']
															.' - '.date($GLOBALS['TL_CONFIG']['datimFormat'],$attendance['tstamp']).$GLOBALS['TL_LANG']['al_frontend']['time'].'"
												>
												<input type="hidden" value="1" name="status">
												<input type="hidden" value="'.$reihe['id'].'" name="m_id">
												<input type="hidden" value="'.$termin['id'].'" name="e_id">
											</form></td>';
										break;
									case 2:
										$att = 
											'<td><form action="#" method="POST">
												<input type="hidden" name="REQUEST_TOKEN" value="{{request_token}}">
												<input 
													type="image" 
													src="system/modules/sb_attendance/assets/img/'.$iconSet.'/no.png"
													alt="'.ucfirst($reihe['username']).': '.$GLOBALS['TL_LANG']['al_frontend']['no']
															.' - '.date($GLOBALS['TL_CONFIG']['datimFormat'],$attendance['tstamp']).$GLOBALS['TL_LANG']['al_frontend']['time'].'"
													title="'.ucfirst($reihe['username']).': '.$GLOBALS['TL_LANG']['al_frontend']['no']
															.' - '.date($GLOBALS['TL_CONFIG']['datimFormat'],$attendance['tstamp']).$GLOBALS['TL_LANG']['al_frontend']['time'].'"
												>
												<input type="hidden" value="2" name="status">
												<input type="hidden" value="'.$reihe['id'].'" name="m_id">
												<input type="hidden" value="'.$termin['id'].'" name="e_id">
											</form></td>';
										break;
									case 3:
										$att = 
											'<td><form action="#" method="POST">
												<input type="hidden" name="REQUEST_TOKEN" value="{{request_token}}">
												<input 
													type="image" 
													src="system/modules/sb_attendance/assets/img/'.$iconSet.'/later.png"
													alt="'.ucfirst($reihe['username']).': '.$GLOBALS['TL_LANG']['al_frontend']['later']
															.' - '.date($GLOBALS['TL_CONFIG']['datimFormat'],$attendance['tstamp']).$GLOBALS['TL_LANG']['al_frontend']['time'].'"
													title="'.ucfirst($reihe['username']).': '.$GLOBALS['TL_LANG']['al_frontend']['later']
															.' - '.date($GLOBALS['TL_CONFIG']['datimFormat'],$attendance['tstamp']).$GLOBALS['TL_LANG']['al_frontend']['time'].'"
												>
												<input type="hidden" value="3" name="status">
												<input type="hidden" value="'.$reihe['id'].'" name="m_id">
												<input type="hidden" value="'.$termin['id'].'" name="e_id">
											</form></td>';
										break;
								}					
							}						
							else
							{
								switch ($att) 
								{
									case 0:
										$att = '<td><img src="system/modules/sb_attendance/assets/img/'.$iconSet.'/unknown.png" 
													alt="'.ucfirst($reihe['username']).': '.$GLOBALS['TL_LANG']['al_frontend']['unknown'].'" 
													title="'.ucfirst($reihe['username']).': '.$GLOBALS['TL_LANG']['al_frontend']['unknown'].'"></td>';
										break;
									case 1:
										$att = '<td><img src="system/modules/sb_attendance/assets/img/'.$iconSet.'/yes.png" 
													alt="'.ucfirst($reihe['username']).': '.$GLOBALS['TL_LANG']['al_frontend']['yes']
															.' - '.date($GLOBALS['TL_CONFIG']['datimFormat'],$attendance['tstamp']).$GLOBALS['TL_LANG']['al_frontend']['time'].'"
													title="'.ucfirst($reihe['username']).': '.$GLOBALS['TL_LANG']['al_frontend']['yes']
															.' - '.date($GLOBALS['TL_CONFIG']['datimFormat'],$attendance['tstamp']).$GLOBALS['TL_LANG']['al_frontend']['time'].'"></td>';
										break;
									case 2:
										$att = '<td><img src="system/modules/sb_attendance/assets/img/'.$iconSet.'/no.png" 
													alt="'.ucfirst($reihe['username']).': '.$GLOBALS['TL_LANG']['al_frontend']['no']
															.' - '.date($GLOBALS['TL_CONFIG']['datimFormat'],$attendance['tstamp']).$GLOBALS['TL_LANG']['al_frontend']['time'].'"
													title="'.ucfirst($reihe['username']).': '.$GLOBALS['TL_LANG']['al_frontend']['no']
															.' - '.date($GLOBALS['TL_CONFIG']['datimFormat'],$attendance['tstamp']).$GLOBALS['TL_LANG']['al_frontend']['time'].'"></td>';
										break;
									case 3:
										$att = '<td><img src="system/modules/sb_attendance/assets/img/'.$iconSet.'/later.png" 
													alt="'.ucfirst($reihe['username']).': '.$GLOBALS['TL_LANG']['al_frontend']['later']
															.' - '.date($GLOBALS['TL_CONFIG']['datimFormat'],$attendance['tstamp']).$GLOBALS['TL_LANG']['al_frontend']['time'].'"
													title="'.ucfirst($reihe['username']).': '.$GLOBALS['TL_LANG']['al_frontend']['later']
															.' - '.date($GLOBALS['TL_CONFIG']['datimFormat'],$attendance['tstamp']).$GLOBALS['TL_LANG']['al_frontend']['time'].'"></td>';
										break;
								}					
							}	
							array_push($stati, $att);
						}
					}
					$k++;
										
				}
				
				
				// Bearbeiten der Daten vor der Übergabe (eingeloggten Nutzer hinzufügen, Username ersten buchstaben groß, 
				// gerade/ungerade Zeilen markieren
				$r += 1;
				$name = ucfirst($name);				
				
				// Trainerrolle Hinweis hinzufügen
				if($coach==$reihe['id'])
				{
					$name = $name." <i>(".$GLOBALS['TL_LANG']['al_frontend']['coach'].")</i>";
				}
				
				// Zeilenbeginn abhängig vom eingeloggten Nutzer erstellen
				if(($logged_user==$reihe['id']) && ($coach==$reihe['id']))
				{		
					if($r%2==1)
					{
						$name = "<tr class='odd logged_user coach'><td class='col_member'>".$name."</td>";
					}
					else
					{
						$name = "<tr class='even logged_user coach'><td class='col_member'>".$name."</td>";
					}
				}
				else if($logged_user==$reihe['id'])
				{		
					if($r%2==1)
					{
						$name = "<tr class='odd logged_user'><td class='col_member'>".$name."</td>";
					}
					else
					{
						$name = "<tr class='even logged_user'><td class='col_member'>".$name."</td>";
					}
				}
				else if ($coach==$reihe['id'])
				{							
					if($r%2==1)
					{
						$name = "<tr class='odd coach'><td class='col_member'>".$name."</td>";
					}
					else
					{
						$name = "<tr class='even coach'><td class='col_member'>".$name."</td>";
					}
				}
				else 
				{							
					if($r%2==1)
					{
						$name = "<tr class='odd'><td class='col_member'>".$name."</td>";
					}
					else
					{
						$name = "<tr class='even'><td class='col_member'>".$name."</td>";
					}
				}
				
				// Daten-Array aufbauen
				$dataArray[] = array(					
					'mitglied' 	=> $name,
					'stati' 	=> $stati
					);
				
			}
			
		
		/**
		 * ENDE: Daten verarbeiten
		 */		
		
		
		/**
		 * Weitergabe der Arrays an das Frontend-Template zur Ausgabe
		 */
		
		$this->Template->tableBody = $dataArray;
		$this->Template->tableHead = $headings;
		
		
		/* 
			Ausgabe von Variablen-Werten
			
			echo "<pre>"; print_r($dataArray); echo "</pre>"; 
		*/
	}
	
	
	
}





