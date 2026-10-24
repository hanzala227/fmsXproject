<?php

namespace Tests\Unit\FrexoAi;

use App\Services\FrexoAi\Validation\AirWeightValidator;
use App\Services\FrexoAi\Validation\ContainerTypeNormalizer;
use App\Services\FrexoAi\Validation\DateLogicValidator;
use App\Services\FrexoAi\Validation\DocumentAuthorityResolver;
use App\Services\FrexoAi\Validation\DuplicateContainerValidator;
use App\Services\FrexoAi\Validation\IataMawbValidator;
use App\Services\FrexoAi\Validation\Iso6346ContainerValidator;
use App\Services\FrexoAi\Validation\NullVsZeroValidator;
use App\Services\FrexoAi\Validation\PortCodeValidator;
use App\Services\FrexoAi\Validation\ScopeIntegrityValidator;
use Tests\TestCase;

class Chunk4DeterministicValidationTest extends TestCase
{
    /**
     * @dataProvider containerCases
     */
    public function test_iso_6346_container_validator(string $input, string $expectedStatus, ?string $expectedCode): void
    {
        $validator = new Iso6346ContainerValidator;
        $result = $validator->validate($input);

        $this->assertEquals($expectedStatus, $result->status);
        if ($expectedCode) {
            $this->assertEquals($expectedCode, $result->code);
        }
    }

    public static function containerCases(): array
    {
        return [
            'valid CSQU3054383' => ['CSQU3054383', 'pass', 'ISO_6346_VALID'],
            'valid lowercase with spaces' => ['csqu 305438-3', 'pass', 'ISO_6346_VALID'],
            'check digit mismatch' => ['CSQU3054389', 'warn', 'CONTAINER_CHECK_DIGIT_MISMATCH'],
            'too short' => ['CSQU30543', 'fail', 'INVALID_CONTAINER_LENGTH'],
            'too long' => ['CSQU305438312', 'fail', 'INVALID_CONTAINER_LENGTH'],
            'invalid characters' => ['12345678901', 'fail', 'INVALID_CONTAINER_FORMAT'],
        ];
    }

    /**
     * @dataProvider mawbCases
     */
    public function test_iata_mawb_validator(string $input, string $expectedStatus, ?string $expectedCode, ?string $expectedFormatted): void
    {
        $validator = new IataMawbValidator;
        $result = $validator->validate($input);

        $this->assertEquals($expectedStatus, $result->status);
        if ($expectedCode) {
            $this->assertEquals($expectedCode, $result->code);
        }
        if ($expectedFormatted) {
            $this->assertEquals($expectedFormatted, $result->normalizedValue);
        }
    }

    public static function mawbCases(): array
    {
        // 1234567 % 7 = 5
        return [
            'valid formatted' => ['176-12345675', 'pass', 'IATA_MAWB_VALID', '176-12345675'],
            'valid unformatted' => ['17612345675', 'pass', 'IATA_MAWB_VALID', '176-12345675'],
            'check digit mismatch' => ['176-12345672', 'warn', 'MAWB_CHECK_DIGIT_MISMATCH', '176-12345672'],
            'too short' => ['176-12345', 'fail', 'INVALID_MAWB_LENGTH', null],
        ];
    }

    public function test_container_type_normalizer(): void
    {
        $normalizer = new ContainerTypeNormalizer;

        // Valid mappings
        $this->assertEquals('40HQ', $normalizer->normalize("40' HIGH CUBE")->normalizedValue);
        $this->assertEquals('40HQ', $normalizer->normalize('40HC')->normalizedValue);
        $this->assertEquals('20GP', $normalizer->normalize("20' STANDARD")->normalizedValue);
        $this->assertEquals('40RF', $normalizer->normalize('40HR')->normalizedValue);

        // Edge unrecognized: keeps raw, warns
        $unrec = $normalizer->normalize('99 SPECIAL TANK');
        $this->assertTrue($unrec->isWarn());
        $this->assertEquals('99 SPECIAL TANK', $unrec->normalizedValue);
    }

    public function test_air_weight_validator(): void
    {
        $validator = new AirWeightValidator(166.67);

        // 1. Volumetric weight calculated: 2 CBM * 166.67 = 333.34 KG
        $res = $validator->validate(200.0, 2.0, null);
        $this->assertEquals(333.34, $res['volume_weight']->normalizedValue);
        $this->assertEquals(333.34, $res['chargeable_weight']->normalizedValue); // max(200, 333.34)

        // 2. Document stated chargeable weight: NEVER overwritten
        $resStated = $validator->validate(500.0, 1.0, 550.0);
        $this->assertEquals(550.0, $resStated['chargeable_weight']->normalizedValue);
        $this->assertTrue($resStated['chargeable_weight']->isPass());

        // 3. Stated lower than minimum warns but keeps stated
        $resLow = $validator->validate(500.0, 1.0, 300.0);
        $this->assertEquals(300.0, $resLow['chargeable_weight']->normalizedValue);
        $this->assertTrue($resLow['chargeable_weight']->isWarn());
    }

    public function test_date_logic_validator(): void
    {
        $validator = new DateLogicValidator;

        // Valid ISO
        $this->assertTrue($validator->validateDate('2026-10-24')->isPass());

        // Ambiguous DD/MM vs MM/DD: 03/04/2026
        $resAmb = $validator->validateDate('03/04/2026');
        $this->assertTrue($resAmb->isWarn());
        $this->assertEquals('AMBIGUOUS_DATE_FORMAT', $resAmb->code);

        // Unambiguous: 25/04/2026 -> 2026-04-25
        $resUnamb = $validator->validateDate('25/04/2026');
        $this->assertTrue($resUnamb->isPass());
        $this->assertEquals('2026-04-25', $resUnamb->normalizedValue);

        // Chronology: ETD > ETA warns without swap
        $chron = $validator->validateChronology('2026-11-01', '2026-10-15');
        $this->assertNotNull($chron);
        $this->assertTrue($chron->isWarn());
        $this->assertEquals('DEPARTURE_AFTER_ARRIVAL', $chron->code);
    }

    public function test_null_vs_zero_validator(): void
    {
        $validator = new NullVsZeroValidator;

        // Null / empty kept as null
        $this->assertNull($validator->validate(null, 'mbl.weight')->normalizedValue);
        $this->assertNull($validator->validate('', 'mbl.weight')->normalizedValue);

        // 0 without evidence warns and keeps null
        $resNoEv = $validator->validate(0, 'mbl.weight', false);
        $this->assertTrue($resNoEv->isWarn());
        $this->assertNull($resNoEv->normalizedValue);

        // 0 with explicit evidence accepted
        $resEv = $validator->validate(0, 'mbl.weight', true);
        $this->assertTrue($resEv->isPass());
        $this->assertEquals(0, $resEv->normalizedValue);
    }

    public function test_duplicate_container_validator(): void
    {
        $validator = new DuplicateContainerValidator;

        $containers = [
            ['container_no' => 'MSCU1234567', 'hbl_no' => 'HBL1'],
            ['container_no' => 'MSCU1234567', 'hbl_no' => 'HBL1'], // same HBL duplicate
            ['container_no' => 'TCNU9999999', 'hbl_no' => 'HBL1'],
            ['container_no' => 'TCNU9999999', 'hbl_no' => 'HBL2'], // across HBLs
        ];

        $results = $validator->validate($containers);

        $this->assertCount(2, $results);
        $this->assertEquals('SUSPECT_DUPLICATE_CONTAINER_SAME_HBL', $results[0]->code);
        $this->assertEquals('CONTAINER_ACROSS_MULTIPLE_HBLS', $results[1]->code);
    }

    public function test_port_code_validator(): void
    {
        $validator = new PortCodeValidator;

        // UNLOCODE
        $this->assertTrue($validator->validate('USLAX', 'UNLOCODE')->isPass());
        $this->assertTrue($validator->validate('CNNBO', 'UNLOCODE')->isPass());
        $this->assertTrue($validator->validate('Shanghai Port', 'UNLOCODE')->isWarn()); // Ambiguous

        // IATA
        $this->assertTrue($validator->validate('JFK', 'IATA')->isPass());
        $this->assertTrue($validator->validate('DXB', 'IATA')->isPass());
        $this->assertTrue($validator->validate('London City Airport', 'IATA')->isWarn()); // Ambiguous
    }

    public function test_scope_integrity_and_authority_resolver(): void
    {
        $scopeValidator = new ScopeIntegrityValidator;

        // Sum matching
        $this->assertTrue($scopeValidator->validateSum(1000.0, [500.0, 500.0])->isPass());

        // Discrepancy > 2%
        $resMismatch = $scopeValidator->validateSum(1000.0, [500.0, 400.0]); // 900 vs 1000 = 10% diff
        $this->assertTrue($resMismatch->isWarn());
        $this->assertEquals('SCOPE_SUM_MISMATCH', $resMismatch->code);

        // Document Authority
        $resolver = new DocumentAuthorityResolver;
        $candidates = [
            ['value' => '15000 KG', 'doc_type' => 'BILL_OF_LADING', 'doc_id' => 'doc_1', 'priority' => 70],
            ['value' => '15200 KG', 'doc_type' => 'PACKING_LIST', 'doc_id' => 'doc_2', 'priority' => 95],
        ];

        $resolved = $resolver->resolve('mbl.gross_weight', 'master', $candidates);

        $this->assertEquals('15200 KG', $resolved['proposed']); // Proposed from Packing List (priority 95)
        $this->assertNotNull($resolved['conflict']); // Emits conflict candidate
        $this->assertCount(2, $resolved['conflict']->candidates);
    }
}
