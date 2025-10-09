<?php

use Zeus\FlatRecord\Decorator\FloatDecorator;
use Zeus\FlatRecord\Delimited\DelimitedRecord;
use Zeus\FlatRecord\Delimited\IndexedField;
use Zeus\FlatRecord\FlatEngine;
use Zeus\FlatRecord\Positional\PositionalField;
use Zeus\FlatRecord\Positional\PositionalRecord;

require __DIR__ . '/../vendor/autoload.php';

#[DelimitedRecord]
class DataTestDelim
{
    #[IndexedField(0)]
    private string $nome = '';
    #[IndexedField(1)]
    public string $endereco;
    #[IndexedField(3)]
    public int    $cpf;
    #[IndexedField(2)]
    public string $telefone;
    
    public function getNome(): string {
        return $this->nome;
    }

    public function setNome(string $nome): void {
        $this->nome = $nome;
    }
}

#[PositionalRecord]
class DataTestPos
{
    #[PositionalField(0, 50)]
    private string $nome = '';
    #[PositionalField(50, 150)]
    public string $endereco;
    #[PositionalField(200, 11, '0', \STR_PAD_LEFT)]
    public int    $cpf;
    #[PositionalField(211, 15, '0', \STR_PAD_LEFT)]
    public string $telefone;
    
    public function getNome(): string {
        return $this->nome;
    }

    public function setNome(string $nome): void {
        $this->nome = $nome;
    }
}

#[PositionalRecord]
class BoletoData
{
    #[PositionalField(0, 3, '0', \STR_PAD_LEFT)]
    public int $banco;
    #[PositionalField(5, 4, '0', \STR_PAD_LEFT)]
    public int $fatorVencto;
    #[PositionalField(9, 10, '0', \STR_PAD_LEFT, new FloatDecorator(2))]
    public float $valor;
    #[PositionalField(19, 25, '0', \STR_PAD_LEFT)]
    public string $campoLivre;
    #[PositionalField(3, 1)]
    public int $moeda;
}

/*$csv = 'Rafael Margado Salvioni,"Rua Felipe Antunes, 187, Casa 2","11-984254845",29707219882';
$obj = new DataTestDelim();
FlatEngine::parse($obj, $csv);
print_r([$obj, FlatEngine::toString($obj)]);

$obj2 = new DataTestPos();
$obj2->setNome($obj->getNome());
$obj2->endereco = $obj->endereco;
$obj2->cpf = $obj->cpf;
$obj2->telefone = $obj->telefone;
print_r([$obj2, FlatEngine::toString($obj2)]);
$str = FlatEngine::toString($obj2);
FlatEngine::parse($obj2, $str);
print_r([$obj2]);*/

$boleto = new BoletoData();
$line = '03395123000000577319721020500009639837500101';
FlatEngine::parseInto($line, $boleto);
print_r($boleto);
echo FlatEngine::toString($boleto);
