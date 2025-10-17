<?php

namespace ZeusTest\FlatRecord;

use LengthException;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use stdClass;
use Zeus\FlatRecord\Decorator\BooleanDecorator;
use Zeus\FlatRecord\Exception\FieldOverwriteException;
use Zeus\FlatRecord\FixedLength\FieldOverlapException;
use Zeus\FlatRecord\FixedLength\FixedLengthField;
use Zeus\FlatRecord\FixedLength\FixedLengthFieldBool;
use Zeus\FlatRecord\FixedLength\FixedLengthFieldFloat;
use Zeus\FlatRecord\FixedLength\FixedLengthFieldInteger;
use Zeus\FlatRecord\FixedLength\FixedLengthFieldNumber;
use Zeus\FlatRecord\FixedLength\FixedLengthRecord;
use Zeus\FlatRecord\FixedLength\PadType;

class FixedLengthTest extends TestCase
{
    /**
     * @test
     */
    public function padTypeTest()
    {
        $original = 'XXXX';
        $length   = 10;
        
        $pad = PadType::LEFT->pad($original, $length);
        $this->assertSame("      $original", $pad);
        $this->assertSame($original, PadType::LEFT->unpad($pad));
        $this->assertSame($pad, PadType::LEFT->truncate("        $pad", $length));
        
        $pad = PadType::RIGHT->pad($original, $length);
        $this->assertSame("$original      ", $pad);
        $this->assertSame($original, PadType::RIGHT->unpad($pad));
        $this->assertSame($pad, PadType::RIGHT->truncate("$pad        ", $length));
        
        $pad = PadType::BOTH->pad($original, $length);
        $this->assertSame("   $original   ", $pad);
        $this->assertSame($original, PadType::BOTH->unpad($pad));
        $this->assertSame($pad, PadType::BOTH->truncate("    $pad    ", $length));
    }
    
    /**
     * @test
     * @depends padTypeTest
     */
    public function baseTest()
    {
        $line = '0009123000000577310000000023456790000000123Teste String            ';
        $parser = new FixedLengthRecord('*', '', true);
        $obj = new stdClass();
        $parser->addField('codbanco', new FixedLengthField(0, 3, false, '0', PadType::LEFT, 0))
            ->addField('moeda', new FixedLengthField(3, 1, false, '0', PadType::LEFT, 0))
            ->addField('vencto', new FixedLengthField(4, 4, false, '0', PadType::LEFT, 0))
            ->addField('valor', new FixedLengthField(8, 10, false, '0', PadType::LEFT, 0))
            ->addField('campoLivre', new FixedLengthField(18, 25, true, '0', PadType::LEFT))
            ->addField('descricao', new FixedLengthField(43, 24, true, ' '));        
        
        $parser->parseInto($line, $obj);
        $this->assertSame(0, $obj->codbanco);
        $this->assertSame('9', $obj->moeda);
        $this->assertSame('1230', $obj->vencto);
        $this->assertSame('57731', $obj->valor);
        $this->assertSame('23456790000000123', $obj->campoLivre);
        $this->assertSame('Teste String', $obj->descricao);
        
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
        //#########Teste     
        $field  = new FixedLengthField(9, 10);
        $obj = (object)['teste' => 'Teste', 'teste2' => null];
        
        $pads = ['#', '?', '-', '*'];
        shuffle($pads);
        $parser = new FixedLengthRecord(padChar:$pads[0], eol:"\n");
        $parser->addField('teste2', new FixedLengthField(12, 8));
        $parser->addField('teste', $field); //overlap field
        $parser->addField('teste3', new FixedLengthField(19, 2)); //overlap field
        $line = $parser->getStringFrom($obj);
        $this->assertSame(22, strlen($line));
        $this->assertSame(str_repeat($pads[0], 9), substr($line, 0, 9));
        $this->assertSame(" \n", substr($line, -2));
        
        $parser->addField('teste3', new FixedLengthField(19, 1)); //overwrite field
        $parser->addField('teste2', new FixedLengthField(12, 5)); //overwrite field
        $line = $parser->getStringFrom($obj);
        $this->assertSame(21, strlen($line));
    }
     
    /**
     * @test
     * @depends baseTest
     */
    public function orderTest()
    {
        //#########Teste     
        $obj = (object)['test1' => 'TEST1', 'test2' => 'TEST2', 'test3' => 'TEST3'];
        
        $parser = new FixedLengthRecord(eol:"");
        $parser->addField('test2', new FixedLengthField(12, 6));
        $parser->addField('test1', new FixedLengthField(9, 6)); //overlap field
        $parser->addField('test3', new FixedLengthField(19, 6));
        $line = $parser->getStringFrom($obj);
        $this->assertSame(25, strlen($line));
        $this->assertTrue(strpos($line, 'TESTEST2') === 9);
        $parser->parseInto($line, $obj);
        $this->assertSame('TESTES', $obj->test1);
        $this->assertSame('TEST2', $obj->test2);
        $this->assertSame('TEST3', $obj->test3);
    }
    
    /**
     * @test
     * @depends parserConfigTest
     */
    public function lenientTest()
    {
        $parser = new FixedLengthRecord(lenient:false);
        try {
            $parser->addField('teste', new FixedLengthField(9, 10));
            $parser->addField('teste2', new FixedLengthField(12, 3)); //overlap field
            $this->assertTrue(false);
        } catch (FieldOverlapException $ex) {
            $this->assertTrue(true);
        }
        $obj = (object)['teste' => 'Teste', 'teste2' => null];
        try {
            $parser->parseInto(str_repeat(' ', 5), $obj);
            $this->assertTrue(false);
        } catch (LengthException $ex) {
            $this->assertTrue(true);
        }
        try {
            $parser->addField('teste', new FixedLengthField(9, 10)); //overwrite
            $this->assertTrue(false);
        } catch (FieldOverwriteException $ex) {
            $this->assertTrue(true);
        }
    }
    
    /**
     * @test
     * @depends baseTest
     */
    public function decoratorTest()
    {
        $parser = new FixedLengthRecord();
        $parser->addField('test', new FixedLengthField(0, 1, decorator: new BooleanDecorator('Y', 'N')));
        $parser->addField('test2', new FixedLengthField(1, 1, decorator: new BooleanDecorator('Y', 'N')));
        $obj = (object)['test' => true, 'test2' => false];
        $line = $parser->getStringFrom($obj);
        $this->assertSame('Y', $line[0]);
        $this->assertSame('N', $line[1]);
        $this->assertSame((array)$obj, $parser->parse($line));
    }
    
    /**
     * @test
     * @depends baseTest
     */
    public function padTest()
    {
        $pads = ['#', '?', '-', '*'];
        shuffle($pads);
        $pad =& $pads[0]; 
        $obj = (object)['test' => 'X', 'test2' => null];
        $tests = [
            [['padChar' => $pad, 'padType' => PadType::LEFT], str_repeat($pad, 4) . 'X'],
            [['padChar' => $pad, 'padType' => PadType::RIGHT], 'X' . str_repeat($pad, 4)],
            [['padChar' => $pad, 'padType' => PadType::BOTH], $pad . $pad . 'X' . $pad . $pad],
        ];
        
        $ref = new ReflectionClass(FixedLengthField::class);
        foreach ($tests as &$test) {
            $parser = new FixedLengthRecord(eol:'');
            $field = $ref->newInstanceArgs([0, 5] + $test[0]);
            $parser->addField('test', $field);
            $line = $parser->getStringFrom($obj);
            $this->assertSame($test[1], $line);
            $parser->parseInto($line, $obj);
            $this->assertSame('X', $obj->test);
        }
    }
    
    /**
     * @test
     * @depends baseTest
     */
    public function truncateTest()
    {
        $obj = (object)['test' => 'X', 'test2' => null];
        $tests = [
            [['padChar' => '1', 'padType' => PadType::LEFT], '1111X', '1111111111111X'],
            [['padChar' => '1', 'padType' => PadType::RIGHT], 'X1111', 'X11111111111111'],
            [['padChar' => '1', 'padType' => PadType::BOTH], '11X11', '1111111X1111111'],
            [['padChar' => '1', 'padType' => PadType::BOTH], '11X11', '1111111X1111111'],
        ];
        
        $ref = new ReflectionClass(FixedLengthField::class);
        foreach ($tests as &$test) {
            $parser = new FixedLengthRecord(eol:'');
            $field = $ref->newInstanceArgs([0, 5] + $test[0] + ['trunc' => true]);
            $parser->addField('test', $field);
            $obj->test = $test[2];
            $line = $parser->getStringFrom($obj);
            $this->assertSame($test[1], $line);
            $parser->parseInto($line, $obj);
            $this->assertSame('X', $obj->test);
        }
        
        $parser = new FixedLengthRecord(eol:'');
        $field = $ref->newInstanceArgs([0, 5] + ['trunc' => false]);
        $parser->addField('test', $field);
        $obj->test = 'rttyuo';
        try {
            $line = $parser->getStringFrom($obj);
            $this->assertTrue(false);
        } catch (LengthException $ex) {
            $this->assertTrue(true);
        }
    }
    
    /**
     * @test
     * @depends baseTest
     */
    public function emptyValueTest()
    {
        $parser = new FixedLengthRecord(eol:'');
        $field = new FixedLengthField(5, 5, empty:'XPTO', padChar:'*');
        $parser->addField('test', $field);
        $line = 'opoel*****sdfgsdghsdf';
        $res = $parser->parse($line);
        $this->assertSame('XPTO', $res['test']);
    }
    
    /**
     * @test
     * @depends baseTest
     */
    public function fieldsTypesTest()
    {
        $parser = new FixedLengthRecord(eol:'');
        $parser->addField('number', new FixedLengthFieldNumber(0, 6))
               ->addField('int', new FixedLengthFieldInteger(6, 6))
               ->addField('float', new FixedLengthFieldFloat(12, 6))
               ->addField('bool', new FixedLengthFieldBool(18, strTrue:'VERDADEIRO', strFalse:'FALSO'));
        $obj = (object)$parser->parse('');
        $obj->number = 8766;
        $obj->int    = 999;
        $obj->float  = 333.33;
        $obj->bool   = false;
        
        $line = $parser->getStringFrom($obj);
        $this->assertSame('008766', \substr($line, 0, 6));
        $this->assertSame('000999', \substr($line, 6, 6));
        $this->assertSame('033333', \substr($line, 12, 6));
        $this->assertSame('FALSO     ', \substr($line, 18, 10));
        $this->assertEquals((array)$obj, $parser->parse($line));
    }
}
