<?php

namespace ZeusTest\FlatRecord;

use LogicException;
use PHPUnit\Framework\TestCase;
use stdClass;
use Zeus\FlatRecord\Decorator\BooleanDecorator;
use Zeus\FlatRecord\Delimited\DelimitedRecord;
use Zeus\FlatRecord\Delimited\IndexedField;
use Zeus\FlatRecord\Exception\FieldOverwriteException;

class DelimitedTest extends TestCase
{
    /**
     * @test
     */
    public function baseTest()
    {
        $line = '"Fullname Surname","Street St, 1234",877883998,9988377849,,,00000';
        $parser = new DelimitedRecord(eol:'');
        $obj = new stdClass();
        $parser->addField('name', new IndexedField(0))
               ->addField('address', new IndexedField(1))
               ->addField('phone', new IndexedField(2))
               ->addField('id', new IndexedField(3))
               ->addField('gap', new IndexedField(6));
        
        $parser->parseInto($line, $obj);
        $this->assertSame('Fullname Surname', $obj->name);
        $this->assertSame('Street St, 1234', $obj->address);
        $this->assertSame('877883998', $obj->phone);
        $this->assertSame('9988377849', $obj->id);
        $this->assertSame('00000', $obj->gap);
        
        $result = $parser->getStringFrom($obj);
        $this->assertSame($line, $result);
        
        $this->assertSame((array)$obj, $parser->parse($line));
    }
    
    /**
     * @test
     * @depends baseTest
     */
    public function parserConfigTest()
    {
        $line = "*Fullname Surname*|*Street St, 1234*|877883998|9988377849\r\r\n";
        $parser = new DelimitedRecord(delimiter:'|', enclosure:'*', eol:"\r\r\n");
        $obj = new stdClass();
        $parser->addField('name', new IndexedField(0))
               ->addField('address', new IndexedField(1))
               ->addField('phone', new IndexedField(2))
               ->addField('id', new IndexedField(3));
        
        $parser->parseInto($line, $obj);
        $this->assertSame('Fullname Surname', $obj->name);
        $this->assertSame('Street St, 1234', $obj->address);
        $this->assertSame('877883998', $obj->phone);
        $this->assertSame('9988377849', $obj->id);
        
        $result = $parser->getStringFrom($obj);
        $this->assertSame($line, $result);
        
        $this->assertSame((array)$obj, $parser->parse($line));
    }
    
    /**
     * @test
     * @depends baseTest
     */
    public function decoratorTest()
    {
        $parser = new DelimitedRecord();
        $parser->addField('test', new IndexedField(0, decorator: new BooleanDecorator('Y', 'N')));
        $parser->addField('test2', new IndexedField(1, decorator: new BooleanDecorator('Y', 'N')));
        $obj = (object)['test' => true, 'test2' => false];
        $line = $parser->getStringFrom($obj);
        $this->assertSame('Y', $line[0]);
        $this->assertSame('N', $line[2]);
        $this->assertSame((array)$obj, $parser->parse($line));
    }
    
    /**
     * @test
     * @depends baseTest
     */
    public function lenientTest()
    {
        $parser = new DelimitedRecord(lenient:false);
        $parser->addField('teste', new IndexedField(0));
        $parser->addField('teste2', new IndexedField(1));
        
        try {
            $parser->addField('teste', new IndexedField(5)); //overwrite by name
            $this->assertTrue(false);
        } catch (FieldOverwriteException $ex) {
            $this->assertTrue(true);
        }
        
        try {
            $parser->addField('foo', new IndexedField(0)); //overwrite by index
            $this->assertTrue(false);
        } catch (FieldOverwriteException $ex) {
            $this->assertTrue(true);
        }
    }
    
    /**
     * @test
     * @depends baseTest
     */
    public function emptyVaueTest()
    {
        $parser = new DelimitedRecord(eol:'');
        $field = new IndexedField(5, empty:'XPTO');
        $parser->addField('test', $field);
        $line = 'a,b,c,d,e,';
        $res = $parser->parse($line);
        $this->assertSame('XPTO', $res['test']);
    }
}
