<?php
declare(strict_types=1);
/**
 * /tests/Integration/Resource/GenericResourceTest.php
 *
 * @author  TLe, Tarmo Leppänen <tarmo.leppanen@protacon.com>
 */
namespace App\Tests\Integration\Resource;

use App\Entity\EntityInterface;
use App\Entity\User as UserEntity;
use App\Repository\UserRepository;
use App\Resource\UserResource;
use App\Rest\DTO\RestDtoInterface;
use App\Rest\DTO\User as UserDto;
use App\Rest\RepositoryInterface;
use App\Rest\ResourceInterface;
use App\Security\Roles;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use PHPUnit_Framework_MockObject_MockBuilder;
use PHPUnit_Framework_MockObject_MockObject;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class GenericResourceTest
 *
 * @package App\Tests\Integration\Resource
 * @author  TLe, Tarmo Leppänen <tarmo.leppanen@protacon.com>
 */
class GenericResourceTest extends KernelTestCase
{
    protected $dtoClass = UserDto::class;
    protected $entityClass = UserEntity::class;
    protected $resourceClass = UserResource::class;
    protected $repositoryClass = UserRepository::class;

    /**
     * @return ValidatorInterface
     */
    private static function getValidator(): ValidatorInterface
    {
        return static::$kernel->getContainer()->get('validator');
    }

    /**
     * @return EntityManagerInterface|Object
     */
    private static function getEntityManager(): EntityManagerInterface
    {
        return static::$kernel->getContainer()->get('doctrine.orm.entity_manager');
    }

    public function setUp(): void
    {
        parent::setUp();

        static::bootKernel();
    }

    /**
     * @expectedException \UnexpectedValueException
     * @expectedExceptionMessageRegExp /DTO class not specified for '.*' resource/
     */
    public function testThatGetDtoClassThrowsAnExceptionWithoutDto(): void
    {
        /** @var PHPUnit_Framework_MockObject_MockObject|UserRepository $repository */
        $repository = $this->getRepositoryMockBuilder()->getMock();

        /** @var ResourceInterface $resource */
        $resource = $this->getResource($repository);
        $resource->setDtoClass('');
        $resource->getDtoClass();
    }

    public function testThatGetDtoClassReturnsExpectedDto(): void
    {
        /** @var PHPUnit_Framework_MockObject_MockObject|UserRepository $repository */
        $repository = $this->getRepositoryMockBuilder()->getMock();

        /** @var ResourceInterface $resource */
        $resource = $this->getResource($repository);
        $resource->setDtoClass('foobar');

        static::assertSame('foobar', $resource->getDtoClass());
    }

    /**
     * @expectedException \UnexpectedValueException
     * @expectedExceptionMessageRegExp /FormType class not specified for '.*' resource/
     */
    public function testGetFormTypeClassThrowsAnExceptionWithoutFormType(): void
    {
        /** @var PHPUnit_Framework_MockObject_MockObject|UserRepository $repository */
        $repository = $this->getRepositoryMockBuilder()->getMock();

        /** @var ResourceInterface $resource */
        $resource = $this->getResource($repository);
        $resource->setFormTypeClass('');
        $resource->getFormTypeClass();
    }

    public function testThatGetFormTypeClassReturnsExpectedDto(): void
    {
        /** @var PHPUnit_Framework_MockObject_MockObject|UserRepository $repository */
        $repository = $this->getRepositoryMockBuilder()->getMock();

        /** @var ResourceInterface $resource */
        $resource = $this->getResource($repository);
        $resource->setFormTypeClass('foobar');

        static::assertSame('foobar', $resource->getFormTypeClass());
    }

    public function testThatGetEntityNameCallsExpectedRepositoryMethod(): void
    {
        /** @var PHPUnit_Framework_MockObject_MockObject|UserRepository $repository */
        $repository = $this->getRepositoryMockBuilder()->getMock();

        $repository
            ->expects(static::once())
            ->method('getEntityName');

        /** @var ResourceInterface $resource */
        $resource = $this->getResource($repository);
        $resource->getEntityName();
    }

    public function testThatGetReferenceCallsExpectedRepositoryMethod(): void
    {
        /** @var PHPUnit_Framework_MockObject_MockObject|UserRepository $repository */
        $repository = $this->getRepositoryMockBuilder()->getMock();

        $repository
            ->expects(static::once())
            ->method('getReference');

        /** @var ResourceInterface $resource */
        $resource = $this->getResource($repository);
        $resource->getReference('some id');
    }

    public function testThatGetAssociationsCallsExpectedRepositoryMethod(): void
    {
        /** @var PHPUnit_Framework_MockObject_MockObject|UserRepository $repository */
        $repository = $this->getRepositoryMockBuilder()->getMock();

        $repository
            ->expects(static::once())
            ->method('getAssociations');

        /** @var ResourceInterface $resource */
        $resource = $this->getResource($repository);
        $resource->getAssociations();
    }

    public function testThatGetDtoForEntityCallsExpectedRepositoryMethod(): void
    {
        $entity = $this->getEntityMock();

        /** @var PHPUnit_Framework_MockObject_MockObject|UserRepository $repository */
        $repository = $this->getRepositoryMockBuilder()->getMock();

        $repository
            ->expects(static::once())
            ->method('find')
            ->with('some id')
            ->willReturn($entity);

        /** @var PHPUnit_Framework_MockObject_MockObject|RestDtoInterface $dto */
        $dto = $this->getDtoMockBuilder()->getMock();

        /** @var ResourceInterface $resource */
        $resource = $this->getResource($repository);

        static::assertInstanceOf(RestDtoInterface::class, $resource->getDtoForEntity('some id', \get_class($dto)));
    }

    /**
     * @expectedException \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @expectedExceptionMessage Not found
     */
    public function testThatGetDtoForEntityThrowsAnExceptionIfEntityWasNotFound(): void
    {
        /** @var PHPUnit_Framework_MockObject_MockObject|UserRepository $repository */
        $repository = $this->getRepositoryMockBuilder()->getMock();

        $repository
            ->expects(static::once())
            ->method('find')
            ->with('some id')
            ->willReturn(null);

        /** @var PHPUnit_Framework_MockObject_MockObject|RestDtoInterface $dto */
        $dto = $this->getDtoMockBuilder()->getMock();

        /** @var ResourceInterface $resource */
        $resource = $this->getResource($repository);
        $resource->getDtoForEntity('some id', \get_class($dto));
    }

    /**
     * @dataProvider dataProviderTestThatFindCallsExpectedRepositoryMethodWithCorrectParameters
     *
     * @param array $expectedArguments
     * @param array $arguments
     */
    public function testThatFindCallsExpectedRepositoryMethodWithCorrectParameters(
        array $expectedArguments,
        array $arguments
    ): void
    {
        /** @var PHPUnit_Framework_MockObject_MockObject|UserRepository $repository */
        $repository = $this->getRepositoryMockBuilder()->getMock();

        $repository
            ->expects(static::once())
            ->method('findByAdvanced')
            ->with(...$expectedArguments);

        /** @var ResourceInterface $resource */
        $resource = $this->getResource($repository);
        $resource->find(...$arguments);
    }

    public function testThatFindOneCallsExpectedRepositoryMethod(): void
    {
        /** @var PHPUnit_Framework_MockObject_MockObject|UserRepository $repository */
        $repository = $this->getRepositoryMockBuilder()->getMock();

        $repository
            ->expects(static::once())
            ->method('find')
            ->withAnyParameters();

        /** @var ResourceInterface $resource */
        $resource = $this->getResource($repository);
        $resource->findOne('some id');
    }

    /**
     * @expectedException \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @expectedExceptionMessage Not found
     */
    public function testThatFindOneThrowsAnExceptionIfEntityWasNotFound(): void
    {
        /** @var PHPUnit_Framework_MockObject_MockObject|UserRepository $repository */
        $repository = $this->getRepositoryMockBuilder()->getMock();

        $repository
            ->expects(static::once())
            ->method('find')
            ->withAnyParameters()
            ->willReturn(null);

        /** @var ResourceInterface $resource */
        $resource = $this->getResource($repository);
        $resource->findOne('some id', true);
    }

    public function testThatFindOneWontThrowAnExceptionIfEntityWasFound(): void
    {
        $entity = $this->getEntityMock();

        /** @var PHPUnit_Framework_MockObject_MockObject|UserRepository $repository */
        $repository = $this->getRepositoryMockBuilder()->getMock();

        $repository
            ->expects(static::once())
            ->method('find')
            ->withAnyParameters()
            ->willReturn($entity);

        /** @var ResourceInterface $resource */
        $resource = $this->getResource($repository);

        static::assertSame($entity, $resource->findOne('some id', true));
    }

    /**
     * @dataProvider dataProviderTestThatFindOneByCallsExpectedRepositoryMethodWithCorrectParameters
     *
     * @param array $expectedArguments
     * @param array $arguments
     */
    public function testThatFindOneByCallsExpectedRepositoryMethodWithCorrectParameters(
        array $expectedArguments,
        array $arguments
    ): void
    {
        /** @var PHPUnit_Framework_MockObject_MockObject|UserRepository $repository */
        $repository = $this->getRepositoryMockBuilder()->getMock();

        $repository
            ->expects(static::once())
            ->method('findOneBy')
            ->with(...$expectedArguments);

        /** @var ResourceInterface $resource */
        $resource = $this->getResource($repository);
        $resource->findOneBy(...$arguments);
    }

    /**
     * @expectedException \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @expectedExceptionMessage Not found
     */
    public function testThatFindOneByThrowsAnExceptionIfEntityWasNotFound(): void
    {
        /** @var PHPUnit_Framework_MockObject_MockObject|UserRepository $repository */
        $repository = $this->getRepositoryMockBuilder()->getMock();

        $repository
            ->expects(static::once())
            ->method('findOneBy')
            ->withAnyParameters()
            ->willReturn(null);

        /** @var ResourceInterface $resource */
        $resource = $this->getResource($repository);
        $resource->findOneBy([], null, true);
    }

    public function testThatFindOneByWontThrowAnExceptionIfEntityWasFound(): void
    {
        $entity = $this->getEntityMock();

        /** @var PHPUnit_Framework_MockObject_MockObject|UserRepository $repository */
        $repository = $this->getRepositoryMockBuilder()->getMock();

        $repository
            ->expects(static::once())
            ->method('findOneBy')
            ->withAnyParameters()
            ->willReturn($entity);

        /** @var ResourceInterface $resource */
        $resource = $this->getResource($repository);

        static::assertSame($entity, $resource->findOneBy([], null, true));
    }

    /**
     * @dataProvider dataProviderTestThatCountCallsExpectedRepositoryMethodWithCorrectParameters
     *
     * @param array $expectedArguments
     * @param array $arguments
     */
    public function testThatCountCallsExpectedRepositoryMethodWithCorrectParameters(
        array $expectedArguments,
        array $arguments
    ): void
    {
        /** @var PHPUnit_Framework_MockObject_MockObject|UserRepository $repository */
        $repository = $this->getRepositoryMockBuilder()->getMock();

        $repository
            ->expects(static::once())
            ->method('countAdvanced')
            ->with(...$expectedArguments);

        /** @var ResourceInterface $resource */
        $resource = $this->getResource($repository);
        $resource->count(...$arguments);
    }

    public function testThatSaveMethodCallsExpectedRepositoryMethod(): void
    {
        $entity = $this->getEntityInterfaceMock();

        /** @var PHPUnit_Framework_MockObject_MockObject|UserRepository $repository */
        $repository = $this->getRepositoryMockBuilder()->getMock();

        $repository
            ->expects(static::once())
            ->method('save')
            ->with($entity);

        /** @var ResourceInterface $resource */
        $resource = $this->getResource($repository);

        static::assertSame($entity, $resource->save($entity));
    }

    /**
     * @expectedException \Symfony\Component\Validator\Exception\ValidatorException
     */
    public function testThatCreateMethodThrowsAnErrorWithInvalidDto(): void
    {
        /** @var PHPUnit_Framework_MockObject_MockObject|UserRepository $repository */
        $repository = $this->getRepositoryMockBuilder()->getMock();

        $dto = new $this->dtoClass();

        /** @var ResourceInterface $resource */
        $resource = $this->getResource($repository);
        $resource->create($dto);
    }

    public function testThatCreateMethodCallsExpectedMethods(): void
    {
        /** @var PHPUnit_Framework_MockObject_MockObject|UserRepository|RepositoryInterface $repository */
        $repository = $this->getRepositoryMockBuilder()->getMock();

        $repository
            ->expects(static::once())
            ->method('getClassName')
            ->willReturn($this->entityClass);

        $repository
            ->expects(static::once())
            ->method('save');

        /** @var PHPUnit_Framework_MockObject_MockObject|ValidatorInterface $validator */
        $validator = $this->getMockBuilder(ValidatorInterface::class)->getMock();

        /** @var PHPUnit_Framework_MockObject_MockObject|RestDtoInterface $dto */
        $dto = $this->getDtoMockBuilder()->getMock();

        $dto
            ->expects(static::once())
            ->method('update');

        /** @var ResourceInterface $resource */
        $resource = $this->getResource($repository, $validator);
        $resource->create($dto);
    }

    /**
     * @expectedException \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @expectedExceptionMessage Not found
     */
    public function testThatUpdateMethodThrowsAnExceptionIfEntityWasNotFound(): void
    {
        /** @var PHPUnit_Framework_MockObject_MockObject|UserRepository $repository */
        $repository = $this->getRepositoryMockBuilder()->getMock();

        $repository
            ->expects(static::once())
            ->method('find')
            ->with('some id')
            ->willReturn(null);

        $dto = new $this->dtoClass();

        /** @var ResourceInterface $resource */
        $resource = $this->getResource($repository);
        $resource->update('some id', $dto);
    }

    public function testThatUpdateCallsExpectedRepositoryMethod(): void
    {
        $dto = new $this->dtoClass();
        $entity = new $this->entityClass();

        $methods = [
            'setUsername'   => 'username',
            'setFirstname'  => 'firstname',
            'setSurname'    => 'surname',
            'setEmail'      => 'test@test.com',
        ];

        foreach ($methods as $method => $value) {
            $dto->$method($value);
            $entity->$method($value);
        }

        /** @var \PHPUnit_Framework_MockObject_MockObject|UserRepository|RepositoryInterface $repository */
        $repository = $this->getRepositoryMockBuilder()->getMock();

        $repository
            ->expects(static::once())
            ->method('find')
            ->with('some id')
            ->willReturn($entity);

        $repository
            ->expects(static::once())
            ->method('save')
            ->with($entity);

        /** @var ResourceInterface $resource */
        $resource = $this->getResource($repository);
        $resource->update('some id', $dto);
    }

    public function testThatDeleteMethodCallsExpectedRepositoryMethod(): void
    {
        $entity = $this->getEntityMock();

        /** @var PHPUnit_Framework_MockObject_MockObject|UserRepository $repository */
        $repository = $this->getRepositoryMockBuilder()->getMock();

        $repository
            ->expects(static::once())
            ->method('find')
            ->with('some id')
            ->willReturn($entity);

        $repository
            ->expects(static::once())
            ->method('remove')
            ->with($entity);

        /** @var ResourceInterface $resource */
        $resource = $this->getResource($repository);

        static::assertSame($entity, $resource->delete('some id'));
    }

    /**
     * @dataProvider dataProviderTestThatGetIdsCallsExpectedRepositoryMethodWithCorrectParameters
     *
     * @param array $expectedArguments
     * @param array $arguments
     */
    public function testThatGetIdsCallsExpectedRepositoryMethodWithCorrectParameters(
        array $expectedArguments,
        array $arguments
    ): void
    {
        /** @var PHPUnit_Framework_MockObject_MockObject|UserRepository $repository */
        $repository = $this->getRepositoryMockBuilder()->getMock();

        $repository
            ->expects(static::once())
            ->method('findIds')
            ->with(...$expectedArguments);

        /** @var ResourceInterface $resource */
        $resource = $this->getResource($repository);
        $resource->getIds(...$arguments);
    }

    /**
     * @expectedException \Symfony\Component\Validator\Exception\ValidatorException
     */
    public function testThatSaveMethodThrowsAnExceptionWithInvalidEntity(): void
    {
        $entity = new $this->entityClass();

        /** @var PHPUnit_Framework_MockObject_MockObject|UserRepository $repository */
        $repository = $this->getRepositoryMockBuilder()->getMock();

        $repository
            ->expects(static::never())
            ->method('save')
            ->with($entity);

        /** @var ResourceInterface $resource */
        $resource = $this->getResource($repository);
        $resource->save($entity);
    }

    /**
     * @return array
     */
    public function dataProviderTestThatCountCallsExpectedRepositoryMethodWithCorrectParameters(): array
    {
        return [
            [
                [[], []],
                [null, null],
            ],
            [
                [['foo'], []],
                [['foo'], null],
            ],
            [
                [['foo'], ['bar']],
                [['foo'], ['bar']],
            ],
        ];
    }

    /**
     * @return array
     */
    public function dataProviderTestThatFindCallsExpectedRepositoryMethodWithCorrectParameters(): array
    {
        return [
            [
                [[], [], 0, 0, []],
                [null, null, null, null, null],
            ],
            [
                [['foo'], [], 0, 0, []],
                [['foo'], null, null, null, null],
            ],
            [
                [['foo'], ['foo'], 0, 0, []],
                [['foo'], ['foo'], null, null, null],
            ],
            [
                [['foo'], ['foo'], 1, 0, []],
                [['foo'], ['foo'], 1, null, null],
            ],
            [
                [['foo'], ['foo'], 1, 2, []],
                [['foo'], ['foo'], 1, 2, null],
            ],
            [
                [['foo'], ['foo'], 1, 2, ['foo']],
                [['foo'], ['foo'], 1, 2, ['foo']],
            ],
        ];
    }

    /**
     * @return array
     */
    public function dataProviderTestThatFindOneByCallsExpectedRepositoryMethodWithCorrectParameters(): array
    {
        return [
            [
                [[], []],
                [[], null],
            ],
            [
                [['foo'], []],
                [['foo'], null],
            ],
            [
                [['foo'], ['bar']],
                [['foo'], ['bar']],
            ],
        ];
    }

    /**
     * @return array
     */
    public function dataProviderTestThatGetIdsCallsExpectedRepositoryMethodWithCorrectParameters(): array
    {
        return [
            [
                [[], []],
                [null, null],
            ],
            [
                [['foo'], []],
                [['foo'], null],
            ],
            [
                [['foo'], ['bar']],
                [['foo'], ['bar']],
            ],
        ];
    }

    /**
     * @return PHPUnit_Framework_MockObject_MockBuilder
     */
    private function getRepositoryMockBuilder(): PHPUnit_Framework_MockObject_MockBuilder
    {
        return $this
            ->getMockBuilder(UserRepository::class)
            ->setConstructorArgs([self::getEntityManager(), new ClassMetadata($this->entityClass)]);
    }

    /**
     * @return PHPUnit_Framework_MockObject_MockObject|EntityInterface
     */
    private function getEntityInterfaceMock(): PHPUnit_Framework_MockObject_MockObject
    {
        return $this
            ->getMockBuilder(EntityInterface::class)
            ->getMock();
    }

    /**
     * @return PHPUnit_Framework_MockObject_MockObject|UserEntity
     */
    private function getEntityMock(): PHPUnit_Framework_MockObject_MockObject
    {
        return $this
            ->getMockBuilder($this->entityClass)
            ->getMock();
    }

    /**
     * @return PHPUnit_Framework_MockObject_MockBuilder
     */
    private function getDtoMockBuilder(): PHPUnit_Framework_MockObject_MockBuilder
    {
        return $this->getMockBuilder($this->dtoClass);
    }

    /**
     * @param RepositoryInterface     $repository
     * @param ValidatorInterface|null $validator
     *
     * @return mixed
     */
    private function getResource(RepositoryInterface $repository, ValidatorInterface $validator = null)
    {
        $roles = $this->getMockBuilder(Roles::class)->disableOriginalConstructor()->getMock();

        $validator = $validator ?? self::getValidator();

        return new $this->resourceClass($repository, $validator, $roles);
    }
}
