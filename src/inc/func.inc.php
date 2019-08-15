<?php

use PhpTal\Php\TalesInternal;
use PhpTal\TalesRegistry;
use Stu\Lib\Db;
use Stu\Orm\Repository\DatabaseEntryRepositoryInterface;

function dbSafe(&$string) {
	return addslashes(str_replace("\"","",$string));
}
class Tuple {

	private $var = NULL;
	private $value = NULL;

	function __construct($var,$value) {
		$this->var = $var;
		$this->value = $value;
	}

	function getVar() {
		return $this->var;
	}
	
	function getValue() {
		return $this->value;
	}
}
function isUseableSkin(&$value) {
	$skins = array(6,7,8,9);
	return in_array($value,$skins);
}
function checkPosition(&$shipa,&$shipb) {
	if ($shipa->isInSystem()) {
		if (!$shipb->isInSystem()) {
			return FALSE;
		}
		if ($shipa->getSystemsId() != $shipb->getSystemsId()) {
			return FALSE;
		}
		if ($shipa->getSX() != $shipb->getSX() || $shipa->getSY() != $shipb->getSY()) {
			return FALSE;
		}
		return TRUE;
	}
	if ($shipa->getCX() != $shipb->getCX() || $shipa->getCY() != $shipb->getCY()) {
		return FALSE;
	}
	return TRUE;
}
function checkColonyPosition($col,$ship) {
	if ($col->getSystemsId() != $ship->getSystemsId()) {
		return FALSE;
	}
	if ($col->getSX() != $ship->getSX() || $col->getSY() != $ship->getSY()) {
		return FALSE;
	}
	return TRUE;
}
function getStorageBar(&$bar,$file,$amount,&$sum) {
	if ($sum < $amount) {
		return;
	}
	$mod = floor($sum/$amount);
	for ($i=1;$i<=$mod;$i++) {
		$bar[] = new Tuple($file.'_'.$amount,FALSE);
	}
	$sum -= $mod*$amount;
}
function noop() {
	return '';
}
function comparePMCategories(&$a,&$b) {
	return strcmp($a->getSort(), $b->getSort());
}
function compareBuildings(&$a,&$b) {
	if ($a->getBuilding()->getId() == $b->getBuilding()->getId()) {
		return $a->getId() > $b->getId();
	}
	return strcmp($a->getBuilding()->getName(), $b->getBuilding()->getName());
}
/**
 */
function tidyString(&$string) { #{{{
	return str_replace(array('<','>','&gt;','&lt;'),'',$string);
} # }}}

function encodeString(&$string) {
	return htmlentities($string,ENT_COMPAT,"UTF-8");
}
function decodeString(&$string,$replaceEntities=TRUE) {
	$string = html_entity_decode($string,ENT_COMPAT,"UTF-8");
	if (!$replaceEntities) {
		return $string;
	}
	return str_replace(array("&"),array("&amp;"),$string);
}
function renderResearchStatusBar($points,&$maxpoints) {
	$pro = getPercentage($points,$maxpoints);
	$bar = getStatusBar(STATUSBAR_BLUE,ceil($pro/2)*2,'Fortschritt: '.$points.'/'.$maxpoints);
	if ($pro < 100) {
		$bar .= getStatusBar(STATUSBAR_GREY,floor((100-$pro)/2)*2,'Fortschritt: '.$points.'/'.$maxpoints);
	}
	return $bar;
}
function renderShieldStatusBar(&$active,&$shields,&$maxshields) {
	$pro = getPercentage($shields,$maxshields);
	$bar = getStatusBar(($active == 1 ? STATUSBAR_BLUE : STATUSBAR_DARKBLUE),ceil($pro/2),'Schilde: '.$shields.'/'.$maxshields);
	if ($pro < 100) {
		$bar .= getStatusBar(STATUSBAR_GREY,floor((100-$pro)/2),'Schilde: '.$shields.'/'.$maxshields);
	}
	return $bar;
}
function getStatusBar($color,$amount,$title='') {
	return '<img src="'.GFX_PATH.'/bars/balken.png" style="background-color: #'.$color.';height: 12px; width:'.round($amount).'px;" title="'.$title.'" />';
}

function getPercentageStatusBar($color,$amount,$maxamount) {
	$pro = getPercentage($amount,$maxamount);
	$bar = getStatusBar($color,ceil($pro/2),'Status: '.$pro.'%');
	if ($pro < 100) {
		$bar .= getStatusBar(STATUSBAR_GREY,floor((100-$pro)/2),'Status: '.$pro.'%');
	}
	return $bar;
}
function getPercentage($val,$maxval) {
	if ($val > $maxval) {
	       $val = $maxval;
	}
	return max(0,@round((100/$maxval)*min($val,$maxval)));
}
function renderHuellStatusBar(&$huell,&$maxhuell) {
	$pro = getPercentage($huell,$maxhuell);
	$bar = getStatusBar(STATUSBAR_GREEN,ceil($pro/2),'Hülle: '.$huell.'/'.$maxhuell);
	if ($pro < 100) {
		$bar .= getStatusBar(STATUSBAR_GREY,floor((100-$pro)/2),'Hülle: '.$huell.'/'.$maxhuell);
	}
	return $bar;
}
function renderStorageStatusBar(&$storage,&$maxstorage) {
	$pro = getPercentage($storage,$maxstorage);
	$bar = getStatusBar(STATUSBAR_GREEN,ceil($pro/2),'Lager: '.$storage.'/'.$maxstorage.' ('.$pro.'%)');
	if ($pro < 100) {
		$bar .= getStatusBar(STATUSBAR_GREY,floor((100-$pro)/2),'Lager: '.$storage.'/'.$maxstorage.' ('.$pro.'%)');
	}
	return $bar;
}
function renderEpsStatusBar(&$eps,&$maxeps) {
	$pro = getPercentage($eps,$maxeps);
	$bar = getStatusBar(STATUSBAR_YELLOW,ceil($pro/2),'Energie: '.$eps.'/'.$maxeps);
	if ($pro < 100) {
		$bar .= getStatusBar(STATUSBAR_GREY,floor((100-$pro)/2),'Energie: '.$eps.'/'.$maxeps);
	}
	return $bar;
}
function bbHandleColor($action, $attributes, $content, $params, &$node_object) {
        if (!isset ($attributes['default'])) {
		return "";
	}
	$color = str_replace(array('&quot;','"'),'',stripslashes($attributes['default']));
        return "<span style=\"color: ".$color."\">".$content."</span>";
}
function getOnlineStatus($online) {
	if ($online) {
		return "online";
	}
	return "offline";
}
function generatePassword($length=6) {
 
	$dummy = array_merge(range('0', '9'), range('a', 'z'), range('A', 'Z'), array('#','&','@','$','_','%','?','+'));
 
	mt_srand((double)microtime()*1000000);

	for ($i = 1; $i <= (count($dummy)*2); $i++) {
		$swap = mt_rand(0,count($dummy)-1);
		$tmp = $dummy[$swap];
		$dummy[$swap] = $dummy[0];
		$dummy[0] = $tmp;
	}
 
	return substr(implode('',$dummy),0,$length);
}
function formatSeconds($time) {
	$h = floor($time / 3600);
	$time -= $h * 3600;
	$m = floor($time / 60);
	$time -= $m * 60;

	$ret = '';
	if ($h > 0) {
		$ret .= $h.'h';
	}
	if ($m > 0) {
		$ret .= ' '.$m.'m';
	}
	if ($time > 0) {
		$ret .= ' '.$time.'s';
	}
	return $ret;
}
function parseDateTime($value) {
	return date("d.m.Y H:i",$value);
}
function parseDate($value) {
	return date("d.m.Y",$value);
}
function &getGoodName($goodId) {
	static $goods = NULL;
	if ($goods === NULL) {
		$goods = Good::getList();
	}
	return $goods[$goodId]->getName();
}
function calculateCosts(&$costs,&$storage,&$place) {
	foreach ($costs as $key => $obj) {
		if (!array_key_exists($key,$storage)) {
			return "Es werden ".$obj->getAmount()." ".getGoodName($obj->getGoodsId())." benötigt - Es ist jedoch keines vorhanden";
		}
		if ($obj->getAmount() > $storage[$key]->getAmount()) {
			return "Es werden ".$obj->getAmount()." ".getGoodName($obj->getGoodsId())." benötigt - Vorhanden sind nur ".$storage[$key]->getAmount();
		}
	}
	reset($costs);
	foreach ($costs as $key => $obj) {
		$place->lowerStorage($obj->getGoodsId(),$obj->getAmount());
	}
	$place->resetStorage();
	return FALSE;
}
function infoToString(&$info) {
	return implode("\n",$info);
}
function getAvailableSlots() {
	return range(1,SLOT_COUNT);
}
function databaseScan(&$database_id,&$user_id) {
	if ($database_id == 0) {
		return;
	}
	DatabaseUser::addEntry($database_id,$user_id);

	// @todo refactor
    global $container;

    $entry = $container->get(DatabaseEntryRepositoryInterface::class)->find($database_id);

	return sprintf(_("Neuer Datenbankeintrag: %s (+%d Punkte)"),$entry->getDescription(),$entry->getCategory()->getPoints());
}
function getUniqId() {
	static $uniqId = NULL;
	if ($uniqId === NULL) {
		$uniqId = microtime(true);
	}
	$uniqId++;
	return $uniqId;
}
/**
 */
function calculateModuleValue($rump,$module,$callback='aggi',$value=FALSE) { #{{{
	if (!$value) {
		$value = $rump->$callback();
	}
	if ($rump->getModuleLevel() > $module->getLevel()) {
		return round($value-$value/100*$module->getDowngradeFactor());
	}
	if ($rump->getModuleLevel() < $module->getLevel()) {
		return round($value+$value/100*$module->getUpgradeFactor());
	}
	return $value;
} # }}}

/**
 */
function calculateDamageImpact(ShipRumpData $rump, ModulesData $module) { #{{{
	if ($rump->getModuleLevel() > $module->getLevel()) {
		return '-'.$module->getDowngradeFactor().'%';
	}
	if ($rump->getModuleLevel() < $module->getLevel()) {
		return '+'.$module->getUpgradeFactor().'%';
	}
	return _('Normal');
} # }}}

/**
 */
function calculateEvadeChance($rump,$module) { #{{{
	$base = $rump->getEvadeChance();
	if ($rump->getModuleLevel() > $module->getLevel()) {
		$value = (1-$base/100) * 1/(1 - $module->getDowngradeFactor()/100);
	} elseif ($rump->getModuleLevel() < $module->getLevel()) {
		$value = (1-$base/100) * 1/(1 + $module->getUpgradeFactor()/100);
	} else {
		return $base;
	}
	return round((1-$value)*100);
} # }}}

function printBackTrace() {
        echo '<div style="background-color:rgb(240,240,240)">';
        foreach (debug_backtrace() as $bt) {
                printf('<pre>%s%s%s(%s) </pre>'
                .'<blockquote>%s:%d</blockquote>',
                        isset($bt['class']) ? $bt['class'] : "",
                        isset($bt['type']) ? $bt['type'] : "",
                        $bt['function'], join(", ", array_map("strval", $bt['args'])),
                        $bt['file'], $bt['line']
                );

        }
        echo "</div>";
}
/**
 */
function createCrewman($userId) { #{{{
	$crew = new CrewData;
	// XXX: For testing purposes
	$crew->setGender(rand(CREW_GENDER_MALE,CREW_GENDER_FEMALE));
	$crew->setType(CREW_TYPE_CREWMAN);
	$crew->setName(_('Crewman'));
	$crew->setRaceId(1);
	$crew->setUserId($userId);
	$crew->save();
	return $crew;
} # }}}
/**
 */
function getContactlistModes() { #{{{
	return array(ContactlistData::CONTACT_FRIEND => array("mode" => ContactlistData::CONTACT_FRIEND,"name" => _("Freund")),
		     ContactlistData::CONTACT_ENEMY => array("mode" => ContactlistData::CONTACT_ENEMY,"name" => _("Feind")),
		     ContactlistData::CONTACT_NEUTRAL => array("mode" => ContactlistData::CONTACT_NEUTRAL,"name" => _("Neutral")));
} # }}}
/**
 */
function isDebugMode() { #{{{
	if (DEBUG_MODE && isAdmin(currentUser()->getId())) {
		return TRUE;
	}
	return FALSE;
} # }}}
/**
 */
function getDefaultTechs() { #{{{
	return array(RESEARCH_START_FEDERATION,RESEARCH_START_ROMULAN,RESEARCH_START_KLINGON,RESEARCH_START_CARDASSIAN,RESEARCH_START_FERENGI,RESEARCH_START_EMPIRE);
} # }}}
/**
 */
function isSystemUser($userId) { #{{{
	$user = array(USER_NOONE);
	return in_array($userId,$user);
} # }}}

/**
 */
function printR($data) { #{{{
	echo "<pre>";
	print_r($data);
	echo "</pre>";
} # }}}

/**
 */
function getModuleLevelClass(ShipRumpData $rump,$module) { #{{{
	if ($rump->getModuleLevels()->{'getModuleLevel'.$module->getModule()->getType()}() > $module->getModule()->getLevel()) {
		return 'module_positive';
	}
	if ($rump->getModuleLevels()->{'getModuleLevel'.$module->getModule()->getType()}() < $module->getModule()->getLevel()) {
		return 'module_negative';
	}
	return '';
} # }}}

/**
 *
 */
function jsquote($str) { #{{{
        return str_replace(
                array(
                        "\\",
                        "'",
                     ),
                array(
                        "\\\\",
                        "\\'",
                     ),
                $str
                );
} #}}}

function &DB() {
    static $DB = NULL;
    if ($DB === NULL) {
        global $container;
        return $container->get(\Stu\Lib\DbInterface::class);
    }
    return $DB;
}

function &getBorderType(&$type) {
    static $borderTypes = array();
    if (!array_key_exists($type,$borderTypes)) {
        $borderTypes[$type] = new Bordertypes($type);
    }
    return $borderTypes[$type];
}
function &getMapType(&$type) {
    static $mapTypes = array();
    if (!array_key_exists($type,$mapTypes)) {
        $mapTypes[$type] = new MapFieldType($type);
    }
    return $mapTypes[$type];
}

function currentUser(): User {
	static $currentUser = NULL;
	if ($currentUser === NULL) {
		global $_SESSION;
		$currentUser = \User::getById($_SESSION['uid']);
	}
	return $currentUser;
}


TalesRegistry::registerPrefix(
    'bbcode',
    function($src, $nothrow): string {
        return 'BBCode()->parse('.TalesInternal::compileToPHPExpression($src,$nothrow).')';
    }
);
TalesRegistry::registerPrefix(
    'jsquote',
    function ($src, $nothrow): string {
        return 'jsquote('.TalesInternal::compileToPHPExpression($src,$nothrow).')';
    }
);
TalesRegistry::registerPrefix(
    'datetime',
    function ($src, $nothrow): string {
        return 'date(\'d.m.Y H:i\', '.TalesInternal::compileToPHPExpression($src,$nothrow).')';
    }
);
TalesRegistry::registerPrefix(
    'nl2br',
    function ($src, $nothrow): string {
        return 'nl2br('.TalesInternal::compileToPHPExpression($src,$nothrow).')';
    }
);
