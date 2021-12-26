<?php
/**
 * Набор методов для работы с highloadblock Bitrix
 * User: darkfriend <hi@darkfriend.ru>
 * Date: 25.04.2017
 * @version 1.0.5
 */

namespace Darkfriend;

use \Bitrix\Highloadblock as HL,
    \Bitrix\Main\Entity,
    \Bitrix\Main\Loader;
use Bitrix\Main\Application;

class HLHelpers
{
    private static $instance;
    public static $LAST_ERROR;

    /**
     * Singleton instance.
     * @param int $iblockID
     * @param string $iblockType
     * @return HLHelpers
     */
    public static function getInstance()
    {
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
    public function getList($arOrder = [], $arFilter = [], $arMoreParams = [])
    {
        $arParams = [];
        if ($arOrder) $arParams['order'] = $arOrder;
        if ($arFilter) $arParams['filter'] = $arFilter;
        if ($arMoreParams) {
            foreach ($arMoreParams as $k => $arMoreParam) {
                $key = \mb_strtolower($k);
                $arParams[$key] = $arMoreParam;
            }
        }
        $rHlblock = HL\HighloadBlockTable::getList($arParams);
        return $rHlblock->fetchAll();
    }

    /**
     * Возвращает HL таблицу
     * @param array $arOrder сортировка
     * @param array $arFilter фильтры
     * @param array $arMoreParams остальные параметры select|group|limit|offset|count_total|runtime|data_doubling
     * @return array
     */
    public function getOne($arOrder=[],$arFilter=[],$arMoreParams=[])
    {
        $arParams = [];
        if($arOrder) $arParams['order'] = $arOrder;
        if($arFilter) $arParams['filter'] = $arFilter;
        if($arMoreParams) {
            foreach ($arMoreParams as $k=>$arMoreParam) {
                $key = \mb_strtolower($k);
                $arParams[$key] = $arMoreParam;
            }
        }
        return HL\HighloadBlockTable::getList($arParams)->fetch();
    }

    /**
     * Проверяет наличие HL таблицы
     * @param array $arFilter фильтры
     * @param array $arMoreParams остальные параметры group|runtime|data_doubling
     * @return bool
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function exists($arFilter=[],$arMoreParams=[])
    {
        $arParams = [];
        if($arFilter) {
            $arParams['filter'] = $arFilter;
        }
        if($arMoreParams) {
            foreach ($arMoreParams as $k=>$arMoreParam) {
                $key = \mb_strtolower($k);
                $arParams[$key] = $arMoreParam;
            }
        }
        $arParams['select'] = ['ID'];
        return !empty(HL\HighloadBlockTable::getList($arParams)->fetchRaw());
    }

    /**
     * Возвращает класс для работы с инфоблоком
     * @param int $hlblockID - идентификатор таблицы HL
     * @return Entity\DataManager|bool
     */
    public function getEntityTable($hlblockID)
    {
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
     * @param array $arMoreParams остальные параметры group|limit|offset|runtime|data_doubling
     * @return \Bitrix\Main\DB\Result
     */
    public function getElementsResource($hlblockID, $arFilter = [], $arOrder = ["ID" => "ASC"], $arSelect = ['*'], $arMoreParams = [])
    {
        $entity = $this->getEntityTable($hlblockID);
        $arParams = [];
        if ($arFilter) $arParams['filter'] = $arFilter;
        if ($arOrder) $arParams['order'] = $arOrder;
        if ($arSelect) $arParams['select'] = $arSelect;
        if ($arMoreParams) {
            foreach ($arMoreParams as $k => $arMoreParam) {
                if (!$arMoreParam) continue;
                $key = \mb_strtolower($k);
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
     * @param array $arMoreParams остальные параметры group|limit|offset|runtime|data_doubling
     * @return array|bool
     */
    public function getElementList($hlblockID, $arFilter = [], $arOrder = ["ID" => "ASC"], $arSelect = ['*'], $arMoreParams = [])
    {
        if (!$hlblockID) return false;
        $rsData = $this->getElementsResource($hlblockID, $arFilter, $arOrder, $arSelect, $arMoreParams);
        $arResult = [];
        while ($arData = $rsData->Fetch()) {
            $arResult[] = $arData;
        }
        return $arResult;
    }

    /**
     * @param int $hlblockID идентификатор таблицы HL
     * @param array $arFilter фильтры
     * @param array $arMoreParams остальные параметры group|runtime|data_doubling
     * @return bool
     */
    public function existsElement($hlblockID, $arFilter=[], $arMoreParams=[])
    {
        if(!$hlblockID) return false;
        $result = $this->getElementsResource($hlblockID,$arFilter,[],['ID'],$arMoreParams)->fetch();
        return !empty($result);
    }

    /**
     * Возвращает общее кол-во строк
     * @param int $hlblockID идентификатор таблицы HL
     * @param array $arFilter фильтры
     * @param array $cache
     * @return int
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @since 1.0.3
     */
    public function getTotalCount($hlblockID, $arFilter=[], $cache=[])
    {
        $entity = $this->getEntityTable($hlblockID);
        return (int) $entity::getCount($arFilter, $cache);
    }

    /**
     * Get one element
     * @param integer $hlblockID
     * @param array $arFilter
     * @param array $arSelect
     * @param array $arMoreParams
     * @return array|false
     */
    public function getElement($hlblockID, $arFilter = [], $arSelect = ['*'], $arMoreParams = [])
    {
        if (!$hlblockID) return false;
        return $this->getElementsResource($hlblockID, $arFilter, [], $arSelect, $arMoreParams)->Fetch();
    }

    /**
     * Get one element by ID
     * @param integer $hlblockID
     * @param integer $id
     * @param array $arMoreParams
     * @return array|false
     */
    public function getElementById($hlblockID, $id, $arMoreParams = [])
    {
        if (!$hlblockID) return false;
        return $this->getElement($hlblockID, ['ID' => $id], [], $arMoreParams);
    }

    /**
     * Создает элемент в хайлоад инфоблоке
     * @param integer $hlblockID - идентификатор таблицы HL
     * @param array $arFields - поля
     * @return bool|int
     */
    public function addElement($hlblockID, $arFields = [])
    {
        if (!$hlblockID || !$arFields) return false;
        $entity = $this->getEntityTable($hlblockID);
        $result = $entity::add($arFields);
        if ($result->isSuccess()) {
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
    public function deleteElement($hlblockID, $ID = null)
    {
        if (!$hlblockID || !$ID) return false;
        $entity = $this->getEntityTable($hlblockID);
        $result = $entity::delete($ID);
        if ($result->isSuccess()) {
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
    public function updateElement($hlblockID, $ID = null, $arFields = [])
    {
        if (!$hlblockID || !$ID || !$arFields) return false;
        $entity = $this->getEntityTable($hlblockID);
        $result = $entity::update($ID, $arFields);
        if ($result->isSuccess()) {
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
    public function getFieldValue($fieldName = '', $fieldID = null)
    {
        $arResult = $this->getFieldValuesList([], [
            'USER_FIELD_NAME' => $fieldName,
            'ID' => $fieldID,
        ]);
        if ($arResult[0]) {
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
    public function getFieldValues($fieldName = '', $arSort = ['SORT' => 'ASC'])
    {
        return $this->getFieldValuesList($arSort, ['USER_FIELD_NAME' => $fieldName]);
    }

    /**
     * Возвращает список всех значений с учетом фильтра и сортировки
     * @param array $arSort сортировка
     * @param array $arFilter условия выборки
     * @return array
     */
    public function getFieldValuesList($arSort = ['SORT' => 'ASC'], $arFilter = [])
    {
        $oFieldEnum = new \CUserFieldEnum;
        $rsValues = $oFieldEnum->GetList($arSort, $arFilter);
        $arResult = [];
        while ($value = $rsValues->Fetch()) {
            $arResult[] = $value;
        }
        return $arResult;
    }

    /**
     * Возвращает значение поля списка по его XML_ID
     * @param string $fieldName название поля UF_NAME
     * @param string $codeName XML_ID значения списка
     * @return bool|array
     */
    public function getFieldValueByCode($fieldName = '', $codeName = '')
    {
        $arResult = $this->getFieldValuesList([], ['USER_FIELD_NAME' => $fieldName, "XML_ID" => $codeName]);
        if ($arResult[0]) return $arResult[0];
        return false;
    }

    /**
     * Создает таблицу для HighloadBlock
     * @param string $nameHLBlock - название HL-блока, должно начинаться с заглавной буквы и состоять только из латинских букв и цифр
     * @param string $tableName - название таблицы для HL-блока, должно состоять только из строчных латинских букв, цифр и знака подчеркивания
     * @return bool|int - id HL-блока
     */
    public function create($nameHLBlock, $tableName)
    {
        $result = HL\HighloadBlockTable::add([
            'TABLE_NAME' => $tableName,
            'NAME' => $nameHLBlock,
        ]);
        $id = false;
        if (!$result->isSuccess()) {
            $msg = $result->getErrorMessages();
            if ($msg) $msg = \implode(\PHP_EOL, $msg);
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
     * @return int
     * @throws $LAST_ERROR
     */
    public function addField($hlblockID, $arFields)
    {
        global $APPLICATION;
        $oUserTypeEntity = new \CUserTypeEntity();
        if (empty($arFields['ENTITY_ID'])) {
            $arFields['ENTITY_ID'] = 'HLBLOCK_' . $hlblockID;
        }
        $id = $oUserTypeEntity->Add($arFields);
        if (!$id) {
            self::$LAST_ERROR = $APPLICATION->GetException();
        }
        return $id;
    }

    /**
     * Возвращает поля таблица
     * @param int $hlblockID - идентификатор таблицы HL
     * @return \Bitrix\Main\ORM\Fields\Field[]|bool
     * @since 1.0.2
     */
    public function getFields($hlblockID)
    {
        if (!$hlblockID) return false;
        $hlblock = HL\HighloadBlockTable::getById($hlblockID)->fetch();
        if (!$hlblock) return false;
        $entity = HL\HighloadBlockTable::compileEntity($hlblock);
        return $entity->getFields();
    }

    /**
     * Обновляет поля свойства по кодовому названию поля (UF_*)
     * @param int $hlblockID идентификатор таблицы HL
     * @param string $ufName код поля
     * @param array $arFields поля, которые нужно обновить
     * @return bool
     * @since 1.0.5
     */
    public function updateFieldByName($hlblockID, $ufName, $arFields)
    {
        if (!$hlblockID || !$ufName) return false;
        $field = \CUserTypeEntity::GetList(
            [],
            [
                'ENTITY_ID' => 'HLBLOCK_' . $hlblockID,
                'FIELD_NAME' => $ufName
            ]
        )->GetNext();

        if(!$field) return false;

        return $this->updateField($hlblockID, $field['ID'], $arFields);
    }

    /**
     * Обновляет поля свойства по id поля
     * @param int $hlblockID идентификатор таблицы HL
     * @param int $fieldId id поля
     * @param array $arFields поля, которые нужно обновить
     * @return bool
     * @since 1.0.5
     */
    public function updateField($hlblockID, $fieldId, $arFields)
    {
        if (!$hlblockID || !$fieldId) return false;
        $oUserTypeEntity = new \CUserTypeEntity();
        return $oUserTypeEntity->Update($fieldId, $arFields);
    }

    /**
     * Удаляет HighloadBlock по $hlblockID
     * @param integer $hlblockID - идентификатор HighloadBlock
     * @return \Bitrix\Main\DB\Result|Entity\DeleteResult
     */
    public function deleteHighloadBlock($hlblockID)
    {
        return HL\HighloadBlockTable::delete($hlblockID);
    }

    /**
     * Удаляет поля из хайлоадблока
     * @param int $hlblockID идентификатор таблицы HL
     * @param array $kFields массив полей к удалению
     * @return bool
     * @since 1.0.3
     * @example \Dev2fun\MultiDomain\HLHelpers::getInstance()
     *     ->removeFields(
     *          10,
     *          ['UF_FIELD_1', 'UF_FIELD_2']
     *      );
     */
    public function removeFields($hlblockID, $kFields)
    {
        global $USER_FIELD_MANAGER;
        if (!$hlblockID) return false;

        // get old data
        $hlblock = HL\HighloadBlockTable::getById($hlblockID)->fetch();

        $fileFields = [];
        $fields = $USER_FIELD_MANAGER->getUserFields(HL\HighloadBlockTable::compileEntityId($hlblockID));
        foreach ($fields as $name => $field) {
            if(!\in_array($name, $kFields)) continue;
            if ($field['USER_TYPE']['BASE_TYPE'] === 'file') {
                $fileFields[] = $name;
            }
        }

        // delete files
        if (!empty($fileFields)) {
            $oldEntity = HL\HighloadBlockTable::compileEntity($hlblock);

            $query = new Entity\Query($oldEntity);

            // select file ids
            $query->setSelect($fileFields);

            // if they are not empty
            $filter = ['LOGIC' => 'OR'];

            foreach ($fileFields as $file_field) {
                $filter['!' . $file_field] = false;
            }

            $query->setFilter($filter);

            // go
            $iterator = $query->exec();

            while ($row = $iterator->fetch()) {
                foreach ($fileFields as $file_field) {
                    if (!empty($row[$file_field])) {
                        if (\is_array($row[$file_field])) {
                            foreach ($row[$file_field] as $value) {
                                \CFile::delete($value);
                            }
                        } else {
                            \CFile::delete($row[$file_field]);
                        }
                    }
                }
            }
            unset($row, $iterator);
        }

        $connection = Application::getConnection();

        foreach ($fields as $name => $field) {
            if(!\in_array($name, $kFields)) continue;
            // delete from uf registry
            if ($field['USER_TYPE']['BASE_TYPE'] === 'enum') {
                $enumField = new \CUserFieldEnum;
                $enumField->DeleteFieldEnum($field['ID']);
            }

            $connection->query("DELETE FROM b_user_field_lang WHERE USER_FIELD_ID = " . $field['ID']);
            $connection->query("DELETE FROM b_user_field WHERE ID = " . $field['ID']);

            // if multiple - drop utm table
            if ($field['MULTIPLE'] == 'Y') {
                $utmTableName = HL\HighloadBlockTable::getMultipleValueTableName($hlblock, $field);
                $connection->dropTable($utmTableName);
            }
        }

        // clear uf cache
        $managedCache = Application::getInstance()->getManagedCache();
        if (\CACHED_b_user_field !== false) {
            $managedCache->cleanDir("b_user_field");
        }

        return true;
    }
}
