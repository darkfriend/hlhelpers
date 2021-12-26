# HLHelpers
Набор методов для работы с highloadblock 1С-Битрикс

Содержание
----
* [Как установить](#install)
* Работа с HighloadBlockTable
    + [Получить все highloadblock](#ListHighloadBlock)
    + [Создать HighloadBlockTable](#CreateHighloadBlock)
    + [Добавить поле в HighloadBlockTable](#AddFieldHighloadBlock)
    + [Обновить поле в HighloadBlockTable по ID](#UpdateFieldHighloadBlock)
    + [Обновить поле в HighloadBlockTable по UF_NAME](#UpdateFieldHighloadBlockByUF)
    + [Удалить поле или поля в HighloadBlockTable](#DeleteFieldHighloadBlock)
    + [Удалить HighloadBlockTable](#DeleteHighloadBlock)
* Работа с элементами
    + [Получить все элементы](#ListElements)
    + [Получить количество строк](#CountElements)
    + [Добавить новый элемент](#AddElement)
    + [Обновить элемент](#UpdateElement)
    + [Удалить элемент](#DelElement)
* Работа с полем вида список
    + [Получить все значения списка](#GetValuesFieldList)
    + [Получить 1 значение списка](#GetValueFieldList)
    + [Получить 1 значение списка по его XML_ID](#GetValueFieldListByXmlId)
* [Гибкость в работе с HighloadBlock](#FlexHighloadBlock)
* [Гибкость в работа с полем "список"](#FlexFieldValuesList)

## <a name="install"></a> Установка

#### Способ 1:
*  Переходим в папку `/local/php_interface/lib/`
* `composer require darkfriend/hlhelpers`
* В файле `/local/php_interface/init.php` пишем ```require __DIR__.'/lib/vendor/autoload.php'```
* Готово

#### Способ 2:
*  Копируем репозиторий в папку `/local/php_interface/lib/`
* В файле `/local/php_interface/init.php` пишем ```require __DIR__.'/lib/hlhelpers/HLHelpers.php'```
* Готово

## Как пользоваться?

### <a name="CreateHighloadBlock"></a> Создать HighloadBlockTable

```php
<?php
    use Darkfriend\HLHelpers;
    $nameHLBlock = 'TestHlBlock';
    $tableName = 'test_table_hl_block';
    $id = HLHelpers::getInstance()->create($nameHLBlock,$tableName);
    print_r($id); // id|false HighloadBlock
    // если $id === false
    // print_r(HLHelpers::$LAST_ERROR);
?>
```

### <a name="AddFieldHighloadBlock"></a> Добавить поле в HighloadBlockTable

```php
<?php
    use Darkfriend\HLHelpers;
    $hlblockID = 1;
    // описание какие данные указывать в $arFields тут https://dev.1c-bitrix.ru/learning/course/?COURSE_ID=43&LESSON_ID=3496
    $arField = [
    	'FIELD_NAME' => 'UF_TEST',
        'USER_TYPE_ID' => 'string',
        'SORT' => '100',
        'MULTIPLE' => 'N',
        'MANDATORY' => 'N',
        'SETTINGS' => [
            'DEFAULT_VALUE' => 'empty',
        ],
        'EDIT_FORM_LABEL' => [
            'ru' => 'Тестовое поле',
            'en' => 'Test field',
        ],
        'LIST_COLUMN_LABEL' => [
            'ru' => 'Тестовое поле',
            'en' => 'Test field',
        ],
    ];
    $id = HLHelpers::getInstance()->addField($hlblockID,$arField);
    print_r($id); // id|false поля
    // если $id === false
    // print_r(HLHelpers::$LAST_ERROR);
?>
```

### <a name="UpdateFieldHighloadBlock"></a> Обновить поле в HighloadBlockTable по ID

```php
<?php
    use Darkfriend\HLHelpers;
    $hlblockID = 1;
    $fieldID = 1;
    $arField = [
        'SORT' => '100',
        'MANDATORY' => 'Y',
        'SETTINGS' => [
            'DEFAULT_VALUE' => 'empty',
        ],
        'EDIT_FORM_LABEL' => [
            'ru' => 'Тестовое поле',
            'en' => 'Test field',
        ],
        'LIST_COLUMN_LABEL' => [
            'ru' => 'Тестовое поле',
            'en' => 'Test field',
        ],
    ];
    $id = HLHelpers::getInstance()->updateField($hlblockID, $fieldID, $arField);
    print_r($id); // bool, как результат
?>
```

### <a name="UpdateFieldHighloadBlockByUF"></a> Обновить поле в HighloadBlockTable по UF_NAME

```php
<?php
    use Darkfriend\HLHelpers;
    $hlblockID = 1;
    $ufName = 'UF_TEST';
    $arField = [
        'SORT' => '100',
        'MANDATORY' => 'Y',
        'SETTINGS' => [
            'DEFAULT_VALUE' => 'empty',
        ],
        'EDIT_FORM_LABEL' => [
            'ru' => 'Тестовое поле',
            'en' => 'Test field',
        ],
        'LIST_COLUMN_LABEL' => [
            'ru' => 'Тестовое поле',
            'en' => 'Test field',
        ],
    ];
    $id = HLHelpers::getInstance()->updateFieldByName($hlblockID, $ufName, $arField);
    print_r($id); // bool, как результат
?>
```

### <a name="DeleteFieldHighloadBlock"></a> Удалить поле или поля в HighloadBlockTable

```php
<?php
    use Darkfriend\HLHelpers;
    $hlblockID = 1;
    $result = HLHelpers::getInstance()->removeFields($hlblockID,[
        'UF_FIELD_1',
        'UF_FIELD_2',
    ]);
    print_r($result); // true|false
?>
```

### <a name="DeleteHighloadBlock"></a> Удалить HighloadBlockTable

```php
<?php
    use Darkfriend\HLHelpers;
    $hlblockID = 1;
    $result = HLHelpers::getInstance()->deleteHighloadBlock($hlblockID);
    print_r($result);
?>
```

### <a name="ListHighloadBlock"></a> Получить все highloadblock

```php
<?php 
    use Darkfriend\HLHelpers;
    $arHL = HLHelpers::getInstance()->getList();
    print_r($arHL);
?>
 ```
 
### <a name="ListElements"></a> Получить все элементы highloadblock

```php
<?php 
    use Darkfriend\HLHelpers;
    $hlID = 1; // идентификатор highloadblock
    
    $arHlElements = HLHelpers::getInstance()->getElementList($hlID);
    print_r($arHlElements);
?>
```

### <a name="CountElements"></a> Получить количество строк в highloadblock

```php
<?php 
    use Darkfriend\HLHelpers;
    $hlID = 1; // идентификатор highloadblock
    $filters = ['UF_FIELD_FIILTER'=>1];
    $totalElements = HLHelpers::getInstance()->getTotalCount($hlID, $filters);
    print_r($totalElements);
?>
```
  
### <a name="AddElement"></a> Добавить новый элемент в highloadblock

```php
<?php 
    use Darkfriend\HLHelpers;
    $hlID = 1; // идентификатор highloadblock
    // массив добавляемых значений, колонка=>значение
    $arFields = [
        'UF_FIELD1' => 'VALUE'
        ...
    ];
    
    $id = HLHelpers::getInstance()->addElement($hlID, $arFields);
    var_dump($id);
    // при false ошибка будет в HLHelpers::$LAST_ERROR
?>
```

### <a name="UpdateElement"></a> Обновить элемент в highloadblock

```php
<?php 
    use Darkfriend\HLHelpers;
    $hlID = 1; // идентификатор highloadblock
    $elID = 1; // идентификатор элемента
    // массив обновляемых значений, колонка=>значение
    $arFields = [
        'UF_FIELD1' => 'VALUE2'
        ...
    ];
    
    $isUpd = HLHelpers::getInstance()->updateElement($hlID, $elID, $arFields);
    var_dump($isUpd);
    // при false ошибка будет в HLHelpers::$LAST_ERROR
?>
```

### <a name="DelElement"></a> Удалить элемент из highloadblock

```php
<?php 
    use Darkfriend\HLHelpers;
    $hlID = 1; // идентификатор highloadblock
    $elID = 1; // идентификатор элемента
    
    $isDel = HLHelpers::getInstance()->deleteElement($hlID, $elID);
    var_dump($isDel);
    // при false ошибка будет в HLHelpers::$LAST_ERROR
?>
```

## Работа с полем вида "список" в highloadblock

### <a name="GetValuesFieldList"></a> Получить все значения поля список у highloadblock

```php
<?php 
    use Darkfriend\HLHelpers;
    $fieldName = "UF_FIELD"; // название поля
    
    $arValues = HLHelpers::getInstance()->getFieldValues($fieldName);
    print_r($arValues);
?>
```

### <a name="GetValueFieldList"></a> Получить значение списка из highloadblock

```php
<?php 
    use Darkfriend\HLHelpers;
    $fieldName = "UF_FIELD"; // название поля
    $valID = 1; // идентификатор значения
    
    $arValue = HLHelpers::getInstance()->getFieldValue($fieldName,$valID);
    print_r($arValue);
?>
```

### <a name="GetValueFieldListByXmlId"></a> Получить значение списка по его XML_ID из highloadblock

```php
<?php 
    use Darkfriend\HLHelpers;
    $fieldName = "UF_FIELD"; // название поля
    $codeName = "CODE_VALUE"; // XML_ID значения
    
    $arValue = HLHelpers::getInstance()->getFieldValueByCode($fieldName,$codeName);
    print_r($arValue);
?>
```

## <a name="FlexHighloadBlock"></a> Гибкость в работе с highloadblock

Для обеспечения лучшей гибкости использовать:
* `getEntityTable($hlblockID)`
* `getElementsResource($hlblockID,$arFilter=[],$arOrder=["ID" => "ASC"],$arSelect=['*'],$arMoreParams=[])`

## <a name="FlexFieldValuesList"></a> Гибкость в работе с полем вида "список" у highloadblock

Для обеспечения лучшей гибкости использовать:
* `getFieldValuesList($arSort=['SORT'=>'ASC'],$arFilter=[])`