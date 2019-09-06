<?php

use Stu\Orm\Repository\MapFieldTypeRepositoryInterface;

class SystemMapData extends BaseTable {

	const tablename = 'stu_sys_map';
	protected $tablename = 'stu_sys_map';

	function __construct(&$data = array()) {
		$this->data = $data;
	}

	function setSX($value) {
		$this->data['sx'] = $value;
		$this->addUpdateField('sx','getSX');
	}

	function getSX() {
		return $this->data['sx'];
	}

	function setSY($value) {
		$this->data['sy'] = $value;
		$this->addUpdateField('sy','getSY');
	}

	function getSY() {
		return $this->data['sy'];
	}

	/**
	 */
	public function setSystemId($value) { # {{{
		$this->setFieldValue('systems_id',$value,'getSystemId');
	} # }}}

	/**
	 */
	public function getSystemId() { # {{{
		return $this->data['systems_id'];
	} # }}}

	public function getFieldId() {
		return $this->data['field_id'];
	}

	public function setFieldId($value) {
		$this->setFieldValue('field_id',$value,'getFieldId');
	}

	public function getFieldType() {
		// @todo refactor
		global $container;

		return $container->get(MapFieldTypeRepositoryInterface::class)->find((int) $this->getFieldId());
	}

	public function hasRegion() {
		return FALSE;
	}

	function getFieldStyle() {
		return "background-image: url('assets/map/".$this->getFieldId().".gif');";
	}
} 
class SystemMap extends SystemMapData {

	function __construct($id=0) {
		$result = DB()->query("SELECT * FROM ".self::tablename." WHERE id=".$id." LIMIT 1",4);
		if ($result == 0) {
			new ObjectNotFoundException($id);
		}
		return parent::__construct($result);
	}

	static function getFieldsByFlightRoute($systemId,$sx,$sy,$ex,$ey) {
		$ret = array();
		if ($sy > $ey) {
			$oy = $sy;
			$sy = $ey;
			$ey = $oy;
		}
		if ($sx > $ex) {
			$ox = $sx;
			$sx = $ex;
			$ex = $ox;
		}
		$result = DB()->query("SELECT * FROM ".self::tablename." WHERE systems_id=".$systemId." AND sx BETWEEN ".$sx." AND ".$ex." AND sy BETWEEN ".$sy." AND ".$ey);
		while ($data = mysqli_fetch_assoc($result)) {
			$ret[$data['sx']."_".$data['sy']] = new SystemMapData($data);
		}
		return $ret;
	}

	static function getFieldByCoords($systemId,$x,$y) {
		$result = DB()->query("SELECT * FROM ".self::tablename." WHERE systems_id=".intval($systemId)." AND sx=".intval($x)." AND sy=".intval($y)." LIMIT 1",4);
		if ($result == 0) {
			return FALSE;
		}
		return new SystemMapData($result);
	}

	/**
	 */
	static function getObjectsBy($sql) { #{{{
		$result = DB()->query("SELECT * FROM ".self::tablename." ".$sql);
		return self::_getList($result,'SystemMapData');
	} # }}}

}
?>
