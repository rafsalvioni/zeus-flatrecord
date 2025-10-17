# zeus-flatrecord

## Introduction
zeus-flatrecord is a API to create, parse, manage and generate flat records. We call "flat records"
a set of structured data stored in one line strings, like CSVs

We can manage these data using a simple OO approach or using associative arrays

## The Basics
After all, we need to define the data mapper structure. It is required for parse and generate data lines.
This could be do by 2 ways: creating a parser object from scratch or creating a mapper class with
special Attributes (like annotations). These Attributes will give the required metadata to create
mapper parsers on demand.

Parsers should implements RecordParserInterface. Fields should implements FieldConfigInterface.

Currently, we have 2 pre-defined record types: DelimitedRecord and FixedLengthRecord.
- **DelimitedRecord**: implements records where its fields are separated using a separator char, like CSV
- **FixedLengthRecord**: implements records where fields has fixed length of chars. So, each field has a fixed
position to begins and ends.

You could do your own record type, if want.

## Creating a record mapper from scratch
```php
// Required and uses ommited

// Delimited Record example
$parser = new DelimitedRecord();
$parser->addField('field1', new IndexedField(0))
       ->addField('field2', new IndexedField(1))
       ->addField('field3', new IndexedField(2));
$csv = 'field1,field2,field3';
$arr = $parser->parse($csv);
print_r($arr); // prints ['field1' => 'field1', 'field2' => 'field2', 'field3' => 'field3']
$arr['field2'] = 'FIELD2';
echo $parser->getStringFrom($arr); // prints 'field1,FIELD2,field3'

// Fixed Length example
$parser = new FixedLengthRecord();
$parser->addField('field1', new FixedLengthField(0, 4))
       ->addField('field2', new FixedLengthField(4, 4))
       ->addField('field3', new FixedLengthField(8, 4))
$fixed = '1   2   3   ';
$arr = $parser->parse($csv);
print_r($arr); // prints ['field1' => '1', 'field2' => '2', 'field3' => '3']
$arr['field2'] = '222';
echo $parser->getStringFrom($arr); // prints '1   222 3   '
```

In both examples above, we create parsers map from scratch. We parses a line to structured data, update
this data and generate a new line. This works fine, but the next approach is better...


## Creating parsers on demand, using Attributes
All record parser classes and fields are Attributes too. Record classes are classes Attributes and
fields definitions are properties Attributes. Using them, our engine could map and creates metadata
records by demand at runtime. Lets see:

```php
// Required and uses ommited

#[DelimitedRecord] // Same classes used above, but like attribute
class DelimitedRecordTest
{
    #[IndexedField(0)] // Maps a property like a field definition
    public $field1;
    #[IndexedField(1)]
    public $field2;
    #[IndexedField(2)]
    public $field3;
}

$csv = 'field1,field2,field3';
$obj = FlatEngine::createFrom($csv, DelimitedRecordTest::class);
print_r($obj); // prints ['field1' => 'field1', 'field2' => 'field2', 'field3' => 'field3']
$obj->field2 = 'FIELD2';
echo FlatEngine::getStringFrom($obj); // prints 'field1,FIELD2,field3'

// Fixed length example
#[FixedLengthRecord]
class FixedLengthTest
{
    #[FixedLengthField(0, 4)]
    public $field1;
    #[FixedLengthField(4, 4)]
    public $field2;
    #[FixedLengthField(8, 4)]
    public $field3;
}

$fixed = '1   2   3   ';
$obj = FlatEngine::createFrom($fixed, FixedLengthTest::class);
print_r($obj); // prints ['field1' => '1', 'field2' => '2', 'field3' => '3']
$obj->field2 = '222';
echo FlatEngine::getStringFrom($obj); // prints '1   222 3   '
```

Above we use other way (and more elegant) to create parsers. One mapper class should use a record type attribute
and each property mapped should have a field definition attribute. One mapped class COULD NOT have 2 record types
defined. Just one will be considered.

The FlatEngine class is the responsable to read a object/class attributes and maps them to parsers definitions

Each record/field class/attribute has its own properties and configurations. See the docs to more details.

## Using Decorators
Sometimes a flat field value needs to be converted to a special data type. For these, we could use decorators.
Decorators should be implements DecoratorInterface and could be attached for any field definition. They does
the in/out conversion of field data. Lets see:

```php
// Required and uses ommited

// Decorator example
#[FixedLengthRecord]
class DecoratorTest
{
    #[FixedLengthField(0, 14, decorator: new DateTimeDecorator('YmdHis'))]
    public DateTimeImmutable $dateTime;
}

$fixed = '20251015000958';
$obj = FlatEngine::createFrom($fixed, DecoratorTest::class);
// prints a DecoratorTest object with $dateTime as DateTimeImmutable with datetime 2025-10-15 00:09:58
print_r($obj);
$obj->dateTime = $obj->dateTime->modify("+5 days");
// prints '20251020000958'
echo FlatEngine::getStringFrom($obj);
```

## Using non-public mapped properties
Sometimes a mapped field should be a non public field. For this cases, FlatEngine could not read and write
object fields. So we needed to give a getter and setter for them. FlatEngine will check if a property has
a getter/setter associated. If yes, it will use them. Else, it try to access property directally.
Getter/Setter should be a pattern name to be accessed. See above:

```php
// Required and uses ommited

#[FixedLengthRecord]
class SetterGetterTest
{
    #[FixedLengthField(0, 5)]
    private $field;

    public function setField($val) {$this->field = $val;} // Correct setter
    public function getField() {return $this->field;} // Correct getter

    public function set_Field($val) {$this->field = $val;} // Doesnt used by FlatEngine
    public function get_Field() {return $this->field;} // Doesnt used by FlatEngine
}

$fixed = '555  ';
$obj = FlatEngine::createFrom($fixed, SetterGetterTest::class);
print_r($obj);
$obj->field = '666'
// prints '666  '
echo FlatEngine::getStringFrom($obj);
```

## Using embedded records
Using records with properties that as records too

```php
#[FixedLengthRecord]
class BaseRecord
{
    #[FixedLengthField(0, 5, decorator: new RecordDecorator(ChildRecord::class))]
    public ChildRecord $child;
}

#[FixedLengthRecord]
class ChildRecord
{
    //class fields....
}

$obj = FlatEngine::createFrom('example line', BaseRecord::class);
// here we can use $obj->child->[child field]
```

