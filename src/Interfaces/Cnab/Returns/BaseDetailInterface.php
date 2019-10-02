<?php

namespace PhpBoleto\Interfaces\Cnab\Returns;

interface BaseDetailInterface
{
    const OCORRENCIA_LIQUIDADA = 1;
    const OCORRENCIA_BAIXADA = 2;
    const OCORRENCIA_ENTRADA = 3;
    const OCORRENCIA_ALTERACAO = 4;
    const OCORRENCIA_PROTESTADA = 5;
    const OCORRENCIA_OUTROS = 6;
    const OCORRENCIA_ERRO = 9;

    /**
     * @return mixed
     */
    public function getOurNumber();

    /**
     * @return mixed
     */
    public function getDocumentNumber();

    /**
     * @return mixed
     */
    public function getOccurrence();

    /**
     * @return mixed
     */
    public function getOccurrenceDescription();

    /**
     * @return mixed
     */
    public function getOccurrenceType();

    /**
     * @param string $format
     *
     * @return mixed
     */
    public function getOccurrenceDate($format = 'd/m/Y');

    /**
     * @param string $format
     *
     * @return mixed
     */
    public function getDueDate($format = 'd/m/Y');

    /**
     * @param string $format
     *
     * @return mixed
     */
    public function getCreditDate($format = 'd/m/Y');

    /**
     * @return mixed
     */
    public function getValue();

    /**
     * @return mixed
     */
    public function getFareValue();

    /**
     * @return mixed
     */
    public function getIOFValue();

    /**
     * @return mixed
     */
    public function getAbatementValue();

    /**
     * @return mixed
     */
    public function getDiscountValue();

    /**
     * @return mixed
     */
    public function getReceivedValue();

    /**
     * @return mixed
     */
    public function getInterestValue();

    /**
     * @return mixed
     */
    public function getFineValue();

    /**
     * @return string
     */
    public function getError();

    /**
     * @return boolean
     */
    public function hasError();

    /**
     * @return boolean
     */
    public function hasOccurrence();

    /**
     * @return array
     */
    public function toArray();
}
