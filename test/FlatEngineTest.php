<?php

namespace ZeusTest\FlatRecord;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use stdClass;
use Zeus\FlatRecord\Decorator\DecoratorInterface;
use Zeus\FlatRecord\Delimited\DelimitedRecord;
use Zeus\FlatRecord\Delimited\IndexedField;
use Zeus\FlatRecord\Exception\ParserDefinitionException;
use Zeus\FlatRecord\FixedLength\FixedLengthField;
use Zeus\FlatRecord\FixedLength\FixedLengthFieldFloat;
use Zeus\FlatRecord\FixedLength\FixedLengthFieldInteger;
use Zeus\FlatRecord\FixedLength\FixedLengthFieldNumber;
use Zeus\FlatRecord\FixedLength\FixedLengthRecord;
use Zeus\FlatRecord\FlatEngine;

class FlatEngineTest extends TestCase
{
    /**
     * @test
     */
    public function delimTest()
    {
        $csv = "\"FirstName Surname\",\"Street Address, 2\",356346354,32523452345";
        $obj = new DataTestDelim();
        FlatEngine::parseInto($csv, $obj);
        $result = FlatEngine::getStringFrom($obj);
        $this->assertSame($csv, $result);
    }
    
    /**
     * @test
     */
    public function fixedTest()
    {
        $boleto = new BoletoBB();
        $line = '0009123000000577310000000023456790000000123';
        $boleto = FlatEngine::createFrom($line, BoletoBB::class);
        $boleto->vencto = new DateTimeImmutable('2025-12-01');
        $boleto->banco = 1;
        $result = FlatEngine::getStringFrom($boleto);
        $this->assertSame('0019128200000577310000000023456790000000123', $result);
    }
    
    /**
     * @test
     */
    public function loadTest()
    {
        $p1 = FlatEngine::load(BoletoBB::class);
        $p2 = FlatEngine::load(BoletoBB::class);
        $this->assertTrue($p1 === $p2); // Tests load cache

        $this->expectException(ParserDefinitionException::class);
        $p1 = FlatEngine::load(stdClass::class);
    }
}

#[DelimitedRecord(",", eol:'')]
class DataTestDelim
{
    #[IndexedField(0)]
    private $nome = '';
    #[IndexedField(1)]
    public $endereco;
    #[IndexedField(3)]
    public $cpf;
    #[IndexedField(2)]
    public int $telefone;
    
    public function getNome(): string {
        return $this->nome;
    }

    public function setNome(string $nome): void {
        $this->nome = $nome;
    }
}

class FatorVenctoDecorator implements DecoratorInterface
{
    private static DateTimeImmutable $dataBase;
    
    public function __construct()
    {
        if (!isset(self::$dataBase)) {
            self::$dataBase = new DateTimeImmutable('1997-10-07');
        }
    }
    
    public function fromString(string $string): mixed {
        $fator = (int)$string;
        if ($fator == 0) {
            return null;
        }
        if ($fator < 9000) {
            $fator += 9000;
        }
        return self::$dataBase->modify("+$fator days");
    }

    public function toString(mixed $value): string {
        if ($value === null) {
            return '';
        }
        $fator  = (int)$value->diff(self::$dataBase)->days;
        $qtde   = (int)($fator / 10000);
        $fator -= (9000 * $qtde);
        return (string)$fator;
    }
}

#[FixedLengthRecord(padChar:'****',eol:'')]
class BoletoData
{
    #[FixedLengthFieldInteger(0, 3, false)]
    public int $banco;
    #[FixedLengthFieldInteger(3, 1, false)]
    public int $moeda;
    #[FixedLengthFieldNumber(4, 4, true, decorator: new FatorVenctoDecorator(), empty:null)]
    public ?DateTimeImmutable $vencto;
    #[FixedLengthFieldFloat(8, 10, precision: 2)]
    public float $valor;
    #[FixedLengthFieldNumber(18, 25, true, empty:'')]
    public string $campoLivre;
}
#[FixedLengthRecord(padChar:'****', lenient:false, eol:'')]
class BoletoBB extends BoletoData
{
    #[FixedLengthFieldNumber(18, 8, true, empty:'')]
    public string $campoLivre;
    #[FixedLengthField(26, 7, true, empty: '')]
    public string $convenio;
    #[FixedLengthFieldNumber(33, 10, true)]
    public int $sequencial;
}

