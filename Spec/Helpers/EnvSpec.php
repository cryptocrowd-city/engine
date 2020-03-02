<?php

namespace Spec\Minds\Helpers;

use Minds\Helpers\Env;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Dotenv;

class EnvSpec extends ObjectBehavior
{
    //Loads env from the same directory as this test
    function let() {
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
        $dotenv->load();
        $dotenv->required('MINDS_ENV_test_int')->isInteger();
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(Env::class);
    }

    function it_gets_minds_env() {
        $config = Env::getMindsEnv();
        expect($config["test"])->toBe('derp');
        expect($config["test_int"])->toBe(4);
        expect($config["test_float"])->toBe(4.45);
        expect($config["test_true"])->toBe(true);
        expect($config["test_false"])->toBe(false);
        expect($config["nested"])->shouldBeArray();
        expect($config["nested"]["array"])->toBe('test');
    }

    function it_casts_bools() {
        expect(Env::cast('true'))->toBe(true);
        expect(Env::cast('false'))->toBe(false);
        expect(Env::cast('trUe'))->toBe(true);
        expect(Env::cast('False'))->toBe(false);
    }

    function it_casts_ints() {
        expect(Env::cast('0'))->toBe(0);
        expect(Env::cast('1'))->toBe(1);
        expect(Env::cast('-99'))->toBe(-99);
    }

    function it_casts_floats() {
        expect(Env::cast('0.05'))->toBe(.05);
        expect(Env::cast('1.2e3'))->toBe(1200);
        expect(Env::cast('-4.34'))->toBe(-4.34);
    }

    function it_handles_errors_and_invalid_types() {
        expect(Env::cast(null))->toBe(null);
        expect(Env::cast([]))->toBe([]);
        $this->shouldThrow(\Exception::class)
            ->during('cast', [(object)[]]);

    }
}
