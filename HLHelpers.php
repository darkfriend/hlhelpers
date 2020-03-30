<?php
/**
 * Набор методов для работы с highloadblock Bitrix
 * User: darkfriend <hi@darkfriend.ru>
 * Date: 25.04.2017
 */

namespace Darkfriend;

use \Bitrix\Highloadblock as HL,
    \Bitrix\Main\Entity,
    \Bitrix\Main\Loader;

class HLHelpers {

    private static $instance;
    public static $LAST_ERROR;

    /**
     * Singleton instance.
     * @param int $iblockID
     * @param string $iblockType
     * @return HLHelpers
     */
    public static function getInstance() {
        if (!self::$instance) {
			Loader::includeModule('highloadblock');
            self::$instance = new HLHelpers();
        }
        return self::$instance;
    }

    /**
     * Возвращает список HL таблиц
     * @param array $arOrder сортировка
     * @param array $arFilter фильтры
     * @param array $arMoreParams остальные параметры select|group|limit|offset|count_total|runtime|data_doubling
     * @return array
     */
    public function getList($arOrder=[],$arFilter=[],$arMoreParams=[]) {
        $arParams = [];
        if($arOrder) $arParams['order'] = $arOrder;
        if($arFilter) $arParams['filter'] = $arFilter;
        if($arMoreParams) {
            foreach ($arMoreParams as $k=>$arMoreParam) {
                $key = mb_strtolower($k);
                $arParams[$key] = $arMoreParam;
            }
        }
        $rHlblock = HL\HighloadBlockTable::getList($arParams);
        return $rHlblock->fetchAll();
    }

    /**
     * Возвращает класс для работы с инфоблоком
     * @param int $hlblockID - идентификатор таблицы HL
     * @return Entity\DataManager|bool
     */
    public function getEntityTable($hlblockID){
        if (!$hlblockID) return false;
        $hlblock = HL\HighloadBlockTable::getById($hlblockID)->fetch();
        if (!$hlblock) return false;
        $entity = HL\HighloadBlockTable::compileEntity($hlblock);
        return $entity->getDataClass();
    }

    /**
     * Возвращает ресурс результата списка элеметнов
     * @param int $hlblockID - идентификатор таблицы HL
     * @param array $arFilter - фильтры
     * @param array $arOrder - сортировка
     * @param array $arSelect - поля, по умолчанию все
     * @param array $arMoreParams остальные параметры group|limit|offset|count_total|runtime|data_doubling
     * @return \Bitrix\Main\DB\Result
     */
    public function getElementsResource($hlblockID,$arFilter=[],$arOrder=["ID" => "ASC"],$arSelect=['*'],$arMoreParams=[]){
        $entity = $this->getEntityTable($hlblockID);
        $arParams = [];
        if($arFilter) $arParams['filter'] = $arFilter;
        if($arOrder) $arParams['order'] = $arOrder;
        if($arSelect) $arParams['select'] = $arSelect;
        if($arMoreParams) {
            foreach ($arMoreParams as $k=>$arMoreParam) {
                if(!$arMoreParam) continue;
                $key = mb_strtolower($k);
                $arParams[$key] = $arMoreParam;
            }
        }
        return $entity::getList($arParams);
    }

    /**
     * Возвращает список эдементов инфоблока
     * @param int $hlblockID - идентификатор таблицы HL
     * @param array $arFilter - фильтры
     * @param array $arOrder - сортировка
     * @param array $arSelect - поля, по умолчанию все
     * @param array $arMoreParams остальные параметры group|limit|offset|count_total|runtime|data_doubling
     * @return array|bool
     */
    public function getElementList($hlblockID,$arFilter=[],$arOrder=["ID" => "ASC"],$arSelect=['*'],$arMoreParams=[]){
        if(!$hlblockID) return false;
        $rsData = $this->getElementsResource($hlblockID,$arFilter,$arOrder,$arSelect,$arMoreParams);
        $arResult = [];
        while($arData = $rsData->Fetch()) {
            $arResult[] = $arData;
        }
        return $arResult;
    }

    /**
     * Get one element
     * @param integer $hlblockID
     * @param array $arFilter
     * @param array $arSelect
     * @param array $arMoreParams
     * @return array|false
     */
    public function getElement($hlblockID, $arFilter=[], $arSelect=['*'], $arMoreParams=[]){
        if(!$hlblockID) return false;
        return $this->getElementsResource($hlblockID,$arFilter,[],$arSelect,$arMoreParams)->Fetch();
    }

    /**
     * Get one element by ID
     * @param integer $hlblockID
     * @param integer $id
     * @param array $arMoreParams
     * @return array|false
     */
    public function getElementById($hlblockID, $id, $arMoreParams=[]){
        if(!$hlblockID) return false;
        return $this->getElement($hlblockID,['ID'=>$id],[],$arMoreParams);
    }

    /**
     * Создает элемент в хайлоад инфоблоке
     * @param integer $hlblockID - идентификатор таблицы HL
     * @param array $arFields - поля
     * @return bool|int
     */
    public function addElement($hlblockID,$arFields=[]){
        if(!$hlblockID||!$arFields) return false;
        $entity = $this->getEntityTable($hlblockID);
        $result = $entity::add($arFields);
        if($result->isSuccess()) {
            return $result->getId();
        } else {
            self::$LAST_ERROR = $result->getErrors();
        }
        return false;
    }

    /**
     * Удаляет элемент из хайлоад инфоблока
     * @param integer $hlblockID - идентификатор таблицы HL
     * @param integer $ID - идентификатор элемента
     * @return bool
     */
    public function deleteElement($hlblockID, $ID=null) {
        if(!$hlblockID||!$ID) return false;
        $entity = $this->getEntityTable($hlblockID);
        $result = $entity::delete($ID);
        if($result->isSuccess()) {
            return true;
        } else {
            self::$LAST_ERROR = $result->getErrors();
        }
        return false;
    }

    /**
     * Обновляет элемент хайлоад инфоблока
     * @param integer $hlblockID - идентификатор таблицы HL
     * @param integer $ID - идентификатор элемента
     * @param array $arFields - обновляемые поля
     * @return bool
     */
    public function updateElement($hlblockID, $ID=null, $arFields=[]) {
        if(!$hlblockID||!$ID||!$arFields) return false;
        $entity = $this->getEntityTable($hlblockID);
        $result = $entity::update($ID, $arFields);
        if($result->isSuccess()) {
            return true;
        } else {
            self::$LAST_ERROR = $result->getErrors();
        }
        return false;
    }

    /**
     * Возвращает значения поля
     * @param string $fieldName название поля UF_NAME
     * @param int $fieldID идентификатор значения
     * @return bool|mixed
     */
    public function getFieldValue($fieldName='',$fieldID=null) {
        $arResult = $this->getFieldValuesList([],[
            'USER_FIELD_NAME'=>$fieldName,
            'ID'=>$fieldID,
        ]);
        if($arResult[0]) {
            return $arResult[0];
        }
        return false;
    }

    /**
     * Возвращает все значения поля $fieldName
     * @param string $fieldName название поля UF_NAME
     * @param array $arSort сортировка
     * @return array
     */
    public function getFieldValues($fieldName='',$arSort=['SORT'=>'ASC']) {
        return $this->getFieldValuesList($arSort,['USER_FIELD_NAME'=>$fieldName]);
    }

    /**
     * Возвращает список всех значений с учетом фильтра и сортировки
     * @param array $arSort сортировка
     * @param array $arFilter условия выборки
     * @return array
     */
    public function getFieldValuesList($arSort=['SORT'=>'ASC'],$arFilter=[]) {
        $oFieldEnum = new \CUserFieldEnum;
        $rsValues = $oFieldEnum->GetList($arSort, $arFilter);
        $arResult = [];
        while($value = $rsValues->Fetch()) {
            $arResult[]=$value;
        }
        return $arResult;
    }

    /**
     * Возвращает значение поля списка по его XML_ID
     * @param string $fieldName название поля UF_NAME
     * @param string $codeName XML_ID значения списка
     * @return bool|array
     */
    public function getFieldValueByCode($fieldName='',$codeName='') {
        $arResult = $this->getFieldValuesList([],['USER_FIELD_NAME'=>$fieldName,"XML_ID"=>$codeName]);
        if($arResult[0]) return $arResult[0];
        return false;
    }

	/**
	 * Создает таблицу для HighloadBlock
	 * @param string $nameHLBlock - название HL-блока, должно начинаться с заглавной буквы и состоять только из латинских букв и цифр
	 * @param string $tableName - название таблицы для HL-блока, должно состоять только из строчных латинских букв, цифр и знака подчеркивания
	 * @return bool|int - id HL-блока
	 */
	public function create($nameHLBlock,$tableName){
		$result = HL\HighloadBlockTable::add([
			'TABLE_NAME' => $tableName,
			'NAME' => $nameHLBlock,
		]);
		$id = false;
		if (!$result->isSuccess()) {
			$msg = $result->getErrorMessages();
			if($msg) $msg = implode(PHP_EOL,$msg);
			self::$LAST_ERROR = $msg;
		} else {
			$id = $result->getId();
		}
		return $id;
	}

	/**
	 * Добавляет поле в HighloadBlock
	 * @param integer $hlblockID - идентификатор HighloadBlock
	 * @param array $arFields - поля, подробности https://dev.1c-bitrix.ru/learning/course/?COURSE_ID=43&LESSON_ID=3496
	 * @throws $LAST_ERROR
	 * @return int
	 */
	public function addField($hlblockID,$arFields) {
		global $APPLICATION;
		$oUserTypeEntity = new \CUserTypeEntity();
		if(empty($arFields['ENTITY_ID'])) {
			$arFields['ENTITY_ID'] = 'HLBLOCK_'.$hlblockID;
		}
		$id = $oUserTypeEntity->Add($arFields);
		if(!$id) {
			self::$LAST_ERROR = $APPLICATION->GetException();
		}
		return $id;
	}

	/**
	 * Удаляет HighloadBlock по $hlblockID
	 * @param integer $hlblockID - идентификатор HighloadBlock
	 * @return \Bitrix\Main\DB\Result|Entity\DeleteResult
	 */
	public function deleteHighloadBlock($hlblockID) {
		return HL\HighloadBlockTable::delete($hlblockID);
	}
}
