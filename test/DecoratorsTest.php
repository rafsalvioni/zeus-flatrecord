<?php

namespace ZeusTest\FlatRecord;

use DateTime;
use PHPUnit\Framework\TestCase;
use Zeus\FlatRecord\Decorator\BooleanDecorator;
use Zeus\FlatRecord\Decorator\DateTimeDecorator;
use Zeus\FlatRecord\Decorator\DecoratorException;
use Zeus\FlatRecord\Decorator\DefaultDecorator;
use Zeus\FlatRecord\Decorator\NumberDecorator;
use Zeus\FlatRecord\Decorator\RecordDecorator;

class DecoratorsTest extends TestCase
{
    /**
     * @test
     */
    public function defaultTest()
    {
        $decorator = new DefaultDecorator();
        $val = 3423;
        $this->assertSame($decorator->toString($val), '3423');
        $this->assertSame($decorator->fromString('3423'), '3423');
        $val = 3423.45;
        $this->assertSame($decorator->toString($val), '3423.45');
        $this->assertSame($decorator->fromString('3423.45'), '3423.45');
        $val = true;
        $this->assertSame($decorator->toString($val), 'true');
        $this->assertSame($decorator->fromString('true'), true);
    }
    
    /**
     * @test
     */
    public function dateTimeTest()
    {
        $format = 'Y-m-d';
        $decorator = new DateTimeDecorator($format);
        $val = new DateTime();
        $str = $decorator->toString($val);
        $val2 = $decorator->fromString($str, 'null');
        $this->assertEquals($val->format($format), $val2->format($format));
    }
    
    /**
     * @test
     */
    public function floatTest()
    {
        $float = 235421.4356;
        $decorator = new NumberDecorator(2);
        $str = $decorator->toString($float);
        $float2 = $decorator->fromString($str);
        $this->assertEquals($float2*100, (float)$str);
        
        $str = '57731';
        $this->assertSame(577.31, $decorator->fromString($str));
        $this->assertSame('57731', $decorator->toString(577.31));
    }
    
    /**
     * @test
     */
    public function boolTest()
    {
        $decorator = new BooleanDecorator('Y', 'N');
        $bool = true;
        $str = $decorator->toString($bool);
        $bool2 = $decorator->fromString($str);
        $this->assertSame($bool, $bool2);
        
        $bool = false;
        $str = $decorator->toString($bool);
        $bool2 = $decorator->fromString($str);
        $this->assertSame($bool, $bool2);
        
        $this->assertSame(null, $decorator->fromString('X'));
        
        $this->expectException(DecoratorException::class);
        $decorator = new BooleanDecorator('Y', 'Y');
    }
    
    /**
     * @test
     */
    public function recordTest()
    {
        $csv = "\"FirstName Surname\",\"Street Address, 2\",356346354,32523452345";
        $decorator = new RecordDecorator(DataTestDelim::class);
        $obj = $decorator->fromString($csv);
        print_r($obj);
        $this->assertTrue(isset($obj->endereco));
        $this->assertSame('Street Address, 2', $obj->endereco);
        $this->assertSame($csv, $decorator->toString($obj));
    }
}
