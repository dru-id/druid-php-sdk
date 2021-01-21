<?php
namespace Genetsis\Tests;

use Genetsis\Identity;
use Genetsis\URLBuilder;
use PHPUnit\Framework\TestCase;

class URLBuilderTest extends TestCase
{

    /**
     * @test
     */
    public function testCompleteAccountWithState()
    {
        Identity::init();

        $generated = URLBuilder::getUrlCompleteAccount('scope', null, 'quepelazotienesmorena');

        fwrite(STDOUT, print_r('---- ' . $generated . ' ----', TRUE));

    }
}