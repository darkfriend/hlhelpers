# HLHelpers
Набор методов для работы с highloadblock 1С-Битрикс

## Как пользоваться?

### Получить все highloadblock

```
<?php 
 use Darkfriend\HLHelpers;
 $arHL = HLHelpers::getInstance()->getList();
 print_r($arHL);
?>
 ```
 
### Получить все элементы highloadblock

```
<?php 
    use Darkfriend\HLHelpers;
    $hlID = 1; // идентификатор highloadblock
    
    $arHlElements = HLHelpers::getInstance()->getElementList($hlID);
    print_r($arHlElements);
?>
```
  
### Добавить новый элемент в highloadblock

```
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

### Обновить элемент в highloadblock

```
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

### Удалить элемент из highloadblock

```
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

### Получить все значения поля список у highloadblock

```
<?php 
    use Darkfriend\HLHelpers;
    $fieldName = "UF_FIELD"; // название поля
    
    $arValues = HLHelpers::getInstance()->getFieldValues($fieldName);
    print_r($arValues);
?>
```

### Получить значение списка из highloadblock

```
<?php 
    use Darkfriend\HLHelpers;
    $fieldName = "UF_FIELD"; // название поля
    $valID = 1; // идентификатор значения
    
    $arValue = HLHelpers::getInstance()->getFieldValue($fieldName,$valID);
    print_r($arValue);
?>
```

### Получить значение списка по его XML_ID из highloadblock

```
<?php 
    use Darkfriend\HLHelpers;
    $fieldName = "UF_FIELD"; // название поля
    $codeName = "CODE_VALUE"; // XML_ID значения
    
    $arValue = HLHelpers::getInstance()->getFieldValueByCode($fieldName,$codeName);
    print_r($arValue);
?>
```

## Гибкость в работе с highloadblock

Для обеспечения лучшей гибкости использовать:
* `getEntityTable($hlblockID)`
* `getElementsResource($hlblockID,$arFilter=[],$arOrder=["ID" => "ASC"],$arSelect=['*'],$arMoreParams=[])`

## Гибкость в работе с полем вида "список" у highloadblock

Для обеспечения лучшей гибкости использовать:
* `getFieldValuesList($arSort=['SORT'=>'ASC'],$arFilter=[])`