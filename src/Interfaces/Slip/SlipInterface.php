<?php

namespace PhpBoleto\Interfaces\Slip;

use DateTimeInterface;
use PhpBoleto\Interfaces\Person\PersonInterface;

interface SlipInterface
{
    const BANK_CODE_BB = '001';
    const BANK_CODE_SANTANDER = '033';
    const BANK_CODE_CEF = '104';
    const BANK_CODE_BRADESCO = '237';
    const BANK_CODE_ITAU = '341';
    const BANK_CODE_HSBC = '399';
    const BANK_CODE_SICREDI = '748';
    const BANK_CODE_BANRISUL = '041';
    const BANK_CODE_BANCOOB = '756';
    const BANK_CODE_BNB = '004';

    const STATUS_REGISTRY = 1;
    const STATUS_ALTER = 2;
    const STATUS_DROP = 3;

    /**
     * Render PDF.
     *
     * @param bool $print
     *
     * @return mixed
     */
    public function getPDF(bool $print = false);

    /**
     * Return boleto as a Array.
     *
     * @return array
     */
    public function toArray(): array;

    /**
     * @return mixed
     */
    public function getDigitableLine(): string;

    /**
     * @return mixed
     */
    public function getBarCode(): string;

    /**
     * @return PersonInterface
     */
    public function getBeneficiary(): PersonInterface;

    /**
     * @return mixed
     */
    public function getBase64Logo(): string;

    /**
     * @return mixed
     */
    public function getLogo(): string;

    /**
     * @return mixed
     */
    public function getBase64BankLogo(): string;

    /**
     * @return mixed
     */
    public function getBankLogo(): string;

    /**
     * @return mixed
     */
    public function getBankCodeWithCheckDigit(): string;

    /**
     * @return int
     */
    public function getCurrency(): int;

    /**
     * @return DateTimeInterface
     */
    public function getDueDate(): ?DateTimeInterface;

    /**
     * @return DateTimeInterface
     */
    public function getDiscountDate(): ?DateTimeInterface;

    /**
     * @return DateTimeInterface
     */
    public function getProcessingDate(): ?DateTimeInterface;

    /**
     * @return DateTimeInterface
     */
    public function getDocumentDate(): ?DateTimeInterface;

    /**
     * @return mixed
     */
    public function getValue(): float;

    /**
     * @return mixed
     */
    public function getDiscount(): float;

    /**
     * @return mixed
     */
    public function getFine(): float;

    /**
     * @return mixed
     */
    public function getInterest(): float;

    /**
     * @return mixed
     */
    public function getChargeInterestAfter(): int;

    /**
     * @param int $default
     *
     * @return mixed
     */
    public function getProtestAfter(int $default = 0): int;

    /**
     * @param int $default
     *
     * @return mixed
     */
    public function getAutomaticDropAfter(int $default = 0);

    /**
     * @return PersonInterface
     */
    public function getGuarantor(): ?PersonInterface;

    /**
     * @return PersonInterface
     */
    public function getPayer(): PersonInterface;

    /**
     * @return mixed
     */
    public function getDemonstrative(): array;

    /**
     * @return mixed
     */
    public function getInstructions(): array;

    /**
     * @return mixed
     */
    public function getPaymentPlace(): string;

    /**
     * @return mixed
     */
    public function getNumber(): int;

    /**
     * @return mixed
     */
    public function getDocumentNumber(): int;

    /**
     * @return mixed
     */
    public function getControlNumber(): ?int;

    /**
     * @return mixed
     */
    public function getAgencyAndAccount(): string;

    /**
     * @return mixed
     */
    public function getOurNumber();

    /**
     * @return mixed
     */
    public function getOurNumberCustom();

    /**
     * @return mixed
     */
    public function getDocumentType(): string;

    /**
     * @param int $default
     * @return mixed
     */
    public function getDocumentTypeCode(int $default = 99);

    /**
     * @return mixed
     */
    public function getAcceptance();

    /**
     * @return mixed
     */
    public function getWallet();

    /**
     * @return mixed
     */
    public function getWalletName(): string;

    /**
     * @return mixed
     */
    public function getBankUsage(): ?string;

    /**
     * @return mixed
     */
    public function getStatus(): int;

    /**
     * @return SlipInterface
     */
    public function alterSlip(): SlipInterface;

    /**
     * @return SlipInterface
     */
    public function dropSlip(): SlipInterface;
}
