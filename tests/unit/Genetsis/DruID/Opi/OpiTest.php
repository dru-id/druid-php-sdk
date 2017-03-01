<?php
namespace UnitTest\Genetsis\DruID\Opi;

use Codeception\Specify;
use Codeception\Test\Unit;
use Genetsis\DruID\Core\OAuth\Beans\OAuthConfig\Api;
use Genetsis\DruID\Core\OAuth\Beans\OAuthConfig\Brand;
use Genetsis\DruID\Core\OAuth\Beans\OAuthConfig\Config;
use Genetsis\DruID\Core\OAuth\Contracts\OAuthServiceInterface;
use Genetsis\DruID\Opi\Opi;
use Genetsis\DruID\UserApi\UserApi;
use Prophecy\Argument;
use Prophecy\Prophet;

/**
 * @package UnitTest\Genetsis\DruID\Opi
 * @category UnitTest
 * @author Ismael Salgado <ismael.salgado@genetsis.com>
 */
class OpiTest extends Unit
{
    use Specify;
    
    /** @var Prophet $prophet */
    private $prophet;

    protected function _before()
    {
        $this->prophet = new Prophet();
    }
    protected function _after()
    {
    }

    public function testGet ()
    {
        $this->specify('Checks if we can get an opi: male|18-24', function() {
            $user_api_proph = $this->prophesize(UserApi::class);
            $user_api_proph->getUserLogged(Argument::cetera())->will(function() {
                $default_user = json_decode(file_get_contents(codecept_data_dir('single-user.json')));
                $default_user->user->user_data->gender->vid = 2; // Male
                $default_user->user->user_data->birthday->value = '31/12/1998'; // 18-24
                return $default_user;
            });
            $oauth_proph = $this->prophesize(OAuthServiceInterface::class);
            $oauth_proph->getConfig()->will(function() {
                return (new Config())
                    ->setBrand((new Brand())->setKey('b1')->setName('Brand 1'))
                    ->addApi((new Api)
                        ->setName('opi')
                        ->setBaseUrl('http://dru-id.foo')
                        ->addEndpoint('rules', 'rules'));
            });

            $object = new Opi(
                $user_api_proph->reveal(),
                $oauth_proph->reveal()
            );
            $this->assertEquals('http://dru-id.foo/rules/my-opi?id=aaaaaaa&sc=Brand+1&carry_url=&carry_sexo=1&carry_edad=1', $object->get('my-opi'));
        });

        $this->specify('Checks if we can get an opi: male|25-34', function() {
            $user_api_proph = $this->prophesize(UserApi::class);
            $user_api_proph->getUserLogged(Argument::cetera())->will(function() {
                $default_user = json_decode(file_get_contents(codecept_data_dir('single-user.json')));
                $default_user->user->user_data->gender->vid = 2; // Male
                $default_user->user->user_data->birthday->value = '31/12/1991'; // 25-34
                return $default_user;
            });
            $oauth_proph = $this->prophesize(OAuthServiceInterface::class);
            $oauth_proph->getConfig()->will(function() {
                return (new Config())
                    ->setBrand((new Brand())->setKey('b1')->setName('Brand 1'))
                    ->addApi((new Api)
                        ->setName('opi')
                        ->setBaseUrl('http://dru-id.foo')
                        ->addEndpoint('rules', 'rules'));
            });

            $object = new Opi(
                $user_api_proph->reveal(),
                $oauth_proph->reveal()
            );
            $this->assertEquals('http://dru-id.foo/rules/my-opi?id=aaaaaaa&sc=Brand+1&carry_url=&carry_sexo=1&carry_edad=2', $object->get('my-opi'));
        });

        $this->specify('Checks if we can get an opi: male|35-44', function() {
            $user_api_proph = $this->prophesize(UserApi::class);
            $user_api_proph->getUserLogged(Argument::cetera())->will(function() {
                $default_user = json_decode(file_get_contents(codecept_data_dir('single-user.json')));
                $default_user->user->user_data->gender->vid = 2; // Male
                $default_user->user->user_data->birthday->value = '31/12/1981'; // 35-44
                return $default_user;
            });
            $oauth_proph = $this->prophesize(OAuthServiceInterface::class);
            $oauth_proph->getConfig()->will(function() {
                return (new Config())
                    ->setBrand((new Brand())->setKey('b1')->setName('Brand 1'))
                    ->addApi((new Api)
                        ->setName('opi')
                        ->setBaseUrl('http://dru-id.foo')
                        ->addEndpoint('rules', 'rules'));
            });

            $object = new Opi(
                $user_api_proph->reveal(),
                $oauth_proph->reveal()
            );
            $this->assertEquals('http://dru-id.foo/rules/my-opi?id=aaaaaaa&sc=Brand+1&carry_url=&carry_sexo=1&carry_edad=3', $object->get('my-opi'));
        });

        $this->specify('Checks if we can get an opi: male|45-64', function() {
            $user_api_proph = $this->prophesize(UserApi::class);
            $user_api_proph->getUserLogged(Argument::cetera())->will(function() {
                $default_user = json_decode(file_get_contents(codecept_data_dir('single-user.json')));
                $default_user->user->user_data->gender->vid = 2; // Male
                $default_user->user->user_data->birthday->value = '31/12/1971'; // 45-64
                return $default_user;
            });
            $oauth_proph = $this->prophesize(OAuthServiceInterface::class);
            $oauth_proph->getConfig()->will(function() {
                return (new Config())
                    ->setBrand((new Brand())->setKey('b1')->setName('Brand 1'))
                    ->addApi((new Api)
                        ->setName('opi')
                        ->setBaseUrl('http://dru-id.foo')
                        ->addEndpoint('rules', 'rules'));
            });

            $object = new Opi(
                $user_api_proph->reveal(),
                $oauth_proph->reveal()
            );
            $this->assertEquals('http://dru-id.foo/rules/my-opi?id=aaaaaaa&sc=Brand+1&carry_url=&carry_sexo=1&carry_edad=4', $object->get('my-opi'));
        });

        $this->specify('Checks if we can get an opi: male|>=65', function() {
            $user_api_proph = $this->prophesize(UserApi::class);
            $user_api_proph->getUserLogged(Argument::cetera())->will(function() {
                $default_user = json_decode(file_get_contents(codecept_data_dir('single-user.json')));
                $default_user->user->user_data->gender->vid = 2; // Male
                $default_user->user->user_data->birthday->value = '31/12/1951'; // >=65
                return $default_user;
            });
            $oauth_proph = $this->prophesize(OAuthServiceInterface::class);
            $oauth_proph->getConfig()->will(function() {
                return (new Config())
                    ->setBrand((new Brand())->setKey('b1')->setName('Brand 1'))
                    ->addApi((new Api)
                        ->setName('opi')
                        ->setBaseUrl('http://dru-id.foo')
                        ->addEndpoint('rules', 'rules'));
            });

            $object = new Opi(
                $user_api_proph->reveal(),
                $oauth_proph->reveal()
            );
            $this->assertEquals('http://dru-id.foo/rules/my-opi?id=aaaaaaa&sc=Brand+1&carry_url=&carry_sexo=1', $object->get('my-opi'));
        });

        $this->specify('Checks if we can get an opi: female|18-24', function() {
            $user_api_proph = $this->prophesize(UserApi::class);
            $user_api_proph->getUserLogged(Argument::cetera())->will(function() {
                $default_user = json_decode(file_get_contents(codecept_data_dir('single-user.json')));
                $default_user->user->user_data->gender->vid = 1; // Female
                $default_user->user->user_data->birthday->value = '31/12/1998'; // 18-24
                return $default_user;
            });
            $oauth_proph = $this->prophesize(OAuthServiceInterface::class);
            $oauth_proph->getConfig()->will(function() {
                return (new Config())
                    ->setBrand((new Brand())->setKey('b1')->setName('Brand 1'))
                    ->addApi((new Api)
                        ->setName('opi')
                        ->setBaseUrl('http://dru-id.foo')
                        ->addEndpoint('rules', 'rules'));
            });

            $object = new Opi(
                $user_api_proph->reveal(),
                $oauth_proph->reveal()
            );
            $this->assertEquals('http://dru-id.foo/rules/my-opi?id=aaaaaaa&sc=Brand+1&carry_url=&carry_sexo=2&carry_edad=1', $object->get('my-opi'));
        });

        $this->specify('Checks if we can get an opi: female|25-34', function() {
            $user_api_proph = $this->prophesize(UserApi::class);
            $user_api_proph->getUserLogged(Argument::cetera())->will(function() {
                $default_user = json_decode(file_get_contents(codecept_data_dir('single-user.json')));
                $default_user->user->user_data->gender->vid = 1; // Female
                $default_user->user->user_data->birthday->value = '31/12/1991'; // 25-34
                return $default_user;
            });
            $oauth_proph = $this->prophesize(OAuthServiceInterface::class);
            $oauth_proph->getConfig()->will(function() {
                return (new Config())
                    ->setBrand((new Brand())->setKey('b1')->setName('Brand 1'))
                    ->addApi((new Api)
                        ->setName('opi')
                        ->setBaseUrl('http://dru-id.foo')
                        ->addEndpoint('rules', 'rules'));
            });

            $object = new Opi(
                $user_api_proph->reveal(),
                $oauth_proph->reveal()
            );
            $this->assertEquals('http://dru-id.foo/rules/my-opi?id=aaaaaaa&sc=Brand+1&carry_url=&carry_sexo=2&carry_edad=2', $object->get('my-opi'));
        });

        $this->specify('Checks if we can get an opi: female|35-64', function() {
            $user_api_proph = $this->prophesize(UserApi::class);
            $user_api_proph->getUserLogged(Argument::cetera())->will(function() {
                $default_user = json_decode(file_get_contents(codecept_data_dir('single-user.json')));
                $default_user->user->user_data->gender->vid = 1; // Female
                $default_user->user->user_data->birthday->value = '31/12/1981'; // 35-64
                return $default_user;
            });
            $oauth_proph = $this->prophesize(OAuthServiceInterface::class);
            $oauth_proph->getConfig()->will(function() {
                return (new Config())
                    ->setBrand((new Brand())->setKey('b1')->setName('Brand 1'))
                    ->addApi((new Api)
                        ->setName('opi')
                        ->setBaseUrl('http://dru-id.foo')
                        ->addEndpoint('rules', 'rules'));
            });

            $object = new Opi(
                $user_api_proph->reveal(),
                $oauth_proph->reveal()
            );
            $this->assertEquals('http://dru-id.foo/rules/my-opi?id=aaaaaaa&sc=Brand+1&carry_url=&carry_sexo=2&carry_edad=3', $object->get('my-opi'));
        });

        $this->specify('Checks if we can get an opi: female|>=65', function() {
            $user_api_proph = $this->prophesize(UserApi::class);
            $user_api_proph->getUserLogged(Argument::cetera())->will(function() {
                $default_user = json_decode(file_get_contents(codecept_data_dir('single-user.json')));
                $default_user->user->user_data->gender->vid = 1; // Female
                $default_user->user->user_data->birthday->value = '31/12/1951'; // >=65
                return $default_user;
            });
            $oauth_proph = $this->prophesize(OAuthServiceInterface::class);
            $oauth_proph->getConfig()->will(function() {
                return (new Config())
                    ->setBrand((new Brand())->setKey('b1')->setName('Brand 1'))
                    ->addApi((new Api)
                        ->setName('opi')
                        ->setBaseUrl('http://dru-id.foo')
                        ->addEndpoint('rules', 'rules'));
            });

            $object = new Opi(
                $user_api_proph->reveal(),
                $oauth_proph->reveal()
            );
            $this->assertEquals('http://dru-id.foo/rules/my-opi?id=aaaaaaa&sc=Brand+1&carry_url=&carry_sexo=2', $object->get('my-opi'));
        });

        $this->specify('Checks if we can get an opi with the default opi code.', function() {
            $user_api_proph = $this->prophesize(UserApi::class);
            $user_api_proph->getUserLogged(Argument::cetera())->will(function() {
                $default_user = json_decode(file_get_contents(codecept_data_dir('single-user.json')));
                $default_user->user->user_data->gender->vid = 2; // Male
                $default_user->user->user_data->birthday->value = '31/12/1998'; // 18-24
                return $default_user;
            });
            $oauth_proph = $this->prophesize(OAuthServiceInterface::class);
            $oauth_proph->getConfig()->will(function() {
                return (new Config())
                    ->setBrand((new Brand())->setKey('b1')->setName('Brand 1'))
                    ->addApi((new Api)
                        ->setName('opi')
                        ->setBaseUrl('http://dru-id.foo')
                        ->addEndpoint('rules', 'rules'))
                    ->setOpi('my-opinator-2');
            });

            $object = new Opi(
                $user_api_proph->reveal(),
                $oauth_proph->reveal()
            );
            $this->assertEquals('http://dru-id.foo/rules/my-opinator-2?id=aaaaaaa&sc=Brand+1&carry_url=&carry_sexo=1&carry_edad=1', $object->get());
        });

        $this->specify('Checks if throws an exception if no valid opinator ID is defined.', function() {
            $oauth_proph = $this->prophesize(OAuthServiceInterface::class);
            $oauth_proph->getConfig()->will(function() {
                return (new Config())
                    ->setBrand((new Brand())->setKey('b1')->setName('Brand 1'))
                    ->addApi((new Api)
                        ->setName('opi')
                        ->setBaseUrl('http://dru-id.foo')
                        ->addEndpoint('rules', 'rules'))
                    ->setOpi('');
            });

            $object = new Opi(
                $this->prophesize(UserApi::class)->reveal(),
                $oauth_proph->reveal()
            );
            $object->get();
        }, ['throws' => \InvalidArgumentException::class]);

        $this->specify('Checks if we can get an opi providing the OID.', function() {
            $user_api_proph = $this->prophesize(UserApi::class);
            $user_api_proph->getUsers(Argument::cetera())->will(function() {
                $default_user = json_decode(file_get_contents(codecept_data_dir('single-user.json')));
                $default_user->user->user_data->gender->vid = 1; // Female
                $default_user->user->user_data->birthday->value = '31/12/1998'; // 18-24
                return [$default_user];
            });
            $oauth_proph = $this->prophesize(OAuthServiceInterface::class);
            $oauth_proph->getConfig()->will(function() {
                return (new Config())
                    ->setBrand((new Brand())->setKey('b1')->setName('Brand 1'))
                    ->addApi((new Api)
                        ->setName('opi')
                        ->setBaseUrl('http://dru-id.foo')
                        ->addEndpoint('rules', 'rules'));
            });

            $object = new Opi(
                $user_api_proph->reveal(),
                $oauth_proph->reveal()
            );
            $this->assertEquals('http://dru-id.foo/rules/my-opi?id=aaaaaaa&sc=Brand+1&carry_url=&carry_sexo=2&carry_edad=1', $object->get('my-opi', null, '123'));
        });

        $this->specify('Checks if redirect URL is properly encoded.', function() {
            $user_api_proph = $this->prophesize(UserApi::class);
            $user_api_proph->getUserLogged(Argument::cetera())->will(function() {
                $default_user = json_decode(file_get_contents(codecept_data_dir('single-user.json')));
                $default_user->user->user_data->gender->vid = 2; // Male
                $default_user->user->user_data->birthday->value = '31/12/1998'; // 18-24
                return $default_user;
            });
            $oauth_proph = $this->prophesize(OAuthServiceInterface::class);
            $oauth_proph->getConfig()->will(function() {
                return (new Config())
                    ->setBrand((new Brand())->setKey('b1')->setName('Brand 1'))
                    ->addApi((new Api)
                        ->setName('opi')
                        ->setBaseUrl('http://dru-id.foo')
                        ->addEndpoint('rules', 'rules'));
            });

            $object = new Opi(
                $user_api_proph->reveal(),
                $oauth_proph->reveal()
            );
            $this->assertEquals('http://dru-id.foo/rules/my-opi?id=aaaaaaa&sc=Brand+1&carry_url=http%3A%2F%2Fdru-id.foo%2Fredirect&carry_sexo=1&carry_edad=1', $object->get('my-opi', 'http://dru-id.foo/redirect'));
        });
    }

    public function testOpen ()
    {
        // NOTE: Opi::open() cannot be tested because it terminates PHP execution with an exit() instruction.
    }
}
