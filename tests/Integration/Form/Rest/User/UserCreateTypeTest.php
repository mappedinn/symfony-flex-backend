<?php
declare(strict_types=1);
/**
 * /tests/Integration/Form/Rest/User/UserCreateTypeTest.php
 *
 * @author  TLe, Tarmo Leppänen <tarmo.leppanen@protacon.com>
 */
namespace App\Tests\Integration\Form\Rest\User;

use App\Form\Rest\User\UserCreateType;
use App\Rest\DTO\User as UserDto;
use Symfony\Component\Form\Test\TypeTestCase;

/**
 * Class UserCreateTypeTest
 *
 * @package App\Tests\Integration\Form\Rest\User
 * @author  TLe, Tarmo Leppänen <tarmo.leppanen@protacon.com>
 */
class UserCreateTypeTest extends TypeTestCase
{
    public function testSubmitValidData(): void
    {
        // Create form
        $form = $this->factory->create(UserCreateType::class);

        // Create new DTO object
        $dto = new UserDto();
        $dto->setUsername('username');
        $dto->setFirstname('John');
        $dto->setSurname('Doe');
        $dto->setEmail('john.doe@test.com');
        $dto->setPassword('some password');

        // Specify used form data
        $formData = array(
            'username'      => 'username',
            'firstname'     => 'John',
            'surname'       => 'Doe',
            'email'         => 'john.doe@test.com',
            'password'      => 'some password',
        );

        // submit the data to the form directly
        $form->submit($formData);

        // Test that data transformers have not been failed
        static::assertTrue($form->isSynchronized());

        // Test that form data matches with the DTO mapping
        static::assertEquals($dto, $form->getData());

        // Check that form renders correctly
        $view = $form->createView();
        $children = $view->children;

        foreach (\array_keys($formData) as $key) {
            static::assertArrayHasKey($key, $children);
        }
    }
}
