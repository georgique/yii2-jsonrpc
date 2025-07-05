<?php

declare(strict_types=1);

use Codeception\Actor;


/**
 * Inherited Methods
 * @method void wantTo($text)
 * @method void wantToTest($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method void pause($vars = [])
 * @method void seeResponseEqualsJson($json = [])
 *
 * @SuppressWarnings(PHPMD)
*/
class Tester extends Actor
{
    use _generated\TesterActions;

    /**
     * Define custom actions here
     */
}
