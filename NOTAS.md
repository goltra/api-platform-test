# GLOSARIO
### IRI

Identificador de Recurso Internacionalizado. Es un identificador único que se usa para identificar un recurso en una api rest.

Por ejemplo, supongamos que tenemos un modelo COCHE y que el id del primer coches es 1, el del segundo 2... y así sucesivamente.
El IRI del primer coche sería /api/coches/1, el del segundo /api/coches/2... y así sucesivamente.

# CREAR EP PERSONALIZADOS

## State provider

api-platform usa los state-provider para las operaciones de obtener datos desde la api y state-processor para las 
operaciones de persistencia de datos. 
Podemos crear un state-provider personalizado para
para cambiar el contenido del la información que nos devuelve la api o para obtener información procedente de otras apis.

Para usar el provider que inyecta doctrine para poder hacer consultas a la base de datos, debemos inyectar el provider
en el constructor del provider personalizado. Luego en el metodo provider podemos obtener los datos y devolverlos
habiendolos procesado previamente.

```php
 public function __construct(
        #[Autowire('@api_platform.doctrine.orm.state.item_provider')]
        private ProviderInterface $itemProvider
    )
    {
    }
```

Luego asignamos como provider en ApiResource el provider que hemos creado
```php
    // Entidad Tiempo
    #[ApiResource(provider: TiempoGetProvider::class)]
````

Si la entidad tiene asignado un controlador personalizado a la misma operación que el provider, prevalece el controlador.

## Creando un controlador personalizado

Podemos crear un controlador personalizado para realizar operaciones que no se ajusten a las operaciones CRUD.
En el ejemplo que dan en la  documentación de symfony entiendo que se crea un controlador por cada acción que queremos
implementar. Entiendo que estos es así porque una api bien diseñada no debería necesitar de forma continua este tipo de
controladores personalizados.

```php
// Entidad Tiempo
#[ApiResource]
#[Get(controller: TiempoController::class)]
class Tiempo
{
    // ...
}

// Controlador personalizado
class TiempoController extends AbstractController
{
   
    public function __invoke(Tiempo $data): Tiempo
    {
        // Modificamos el objeto tiempo
        $data->setDescripcion('llamado desde controlador personalizado');
        return $data;
    }
}
```

# CREAR VALIDADORES PERSONALIZADOS

Creamos un constraint y un constraintValidator. Por la nomenclatura de symfony, el nombre del constraintValidator debe
ser el nombre del constraint seguido de Validator.

```php
#[\Attribute]
class MinimalLenght extends Constraint{
    public $message = 'El valor debe tener al menos 10 caracteres.';
}


final class MinimalLenghtValidator extends ConstraintValidator{
    public function validate($value, Constraint $constraint): void
    {
        if (strlen($value) < 10){
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }
}
```
Asignamos el validador al campo que queramos validar.
```php
    //Tiempo entity

    #[MinimalLenght]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $descripcion = null;
```

# ENTIDADES

## GRUPOS DE NORMALIZACIÓN Y DENORMALIZACIÓN
**Ojo, la version api platform 3.2.15 no funciona con la version doctrine/orm superior a  2.18.** En github parece que
esto se va a resolver en la version api platform 3.3.0. Mientras tanto hay que hacer downgrade de doctrine/orm a 2.18.0.
https://github.com/api-platform/core/issues/6199

Las entidades de symfony pasan de json a objeto y objeto a json usando un array intermedio y nomalizando (array->objeto) y denormalizando (objeto->array) los datos.
La denormalizacion forma parte del proceso de deserialización donde un texto (json, xml..) pasa a objeto y al contrario
la normalización forma parte del proceso de serialización donde un objeto pasa a texto.

Dicho esto podemos crear grupos de normalización y denormalización  y usar esto para que ciertos atributos de nuestra entidad
no se serialicen o deserialicen lo que nos va a permitir definir si son de lectura, escritura o ambos.

```php

#[ApiResource(
    normalizationContext: ['groups' => ['tiempo:read']],
    denormalizationContext: ['groups' => ['tiempo:write']],
)]

class Tiempo
{
    // ...
    #[Groups(['tiempo:read', 'tiempo:write'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $descripcion = null;
}
```
## RELACIONES
Para crear una relación entre dos entidades, por ejemplo una relación de uno a muchos, debemos definir la relación en 
ambas entidades.

**Ojo que si usamos las migraciones de doctrine, el campo que va a crear en la tabla le añade el sufijo _id al nombre del campo.** 
En el ejemplo que tenemos abajo, el campo de la tabla Tiempo en realidad no es user sino user_id.
```php
class User
{
    // ...
    #[ORM\OneToMany(targetEntity: Tiempo::class, mappedBy: 'user')]
    #[Groups(['user:read'])]
    private $tiempos;
}

class Tiempo
{
    // ...
    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'tiempos')]
    #[ORM\JoinColumn(nullable: false)]
    private $user;
}
```
Como esto hemos conseguido obtener los tiempos relacionados cuando obtenemos un usuario pero esa relación nos muestra
el IRI del recurso relacionado. Si queremos que nos muestre el contenido del recurso relacionado en lugar del IRI, debemos añadir a 
cada atributo de la entidad tiempo el grupo de denormalización del user.
```php
class Tiempo
{
    // ...
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['tiempo:read', 'tiempo:write','user:read'])]
    private ?\DateTimeInterface $fin = null;
    
    #[MinimalLenght]
    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['tiempo:write','user:read'])]
    private ?string $descripcion = null;
}
```
# HASH DE CONTRASEÑA DEL USUARIO

Para hashear la contraseña del usuario necesitamos procesar la contraseña en texto plano antes de guardarla en la base de datos.
El hasher usado se configura en el fichero security.yaml.

    ```yaml
    password_hashers:
        #Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface: 'auto'
        App\Entity\User: 'auto'
    ```
Podemos definir también una configuración diferente para el entorno de pruebas:

    ```yaml
    when@test:
    security:
        password_hashers:
            Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface:
                algorithm: auto
                cost: 4 # Lowest possible value for bcrypt
                time_cost: 3 # Lowest possible value for argon
                memory_cost: 10 # Lowest possible value for argon
    ```

Creamos un state processor para usar en la entidad user (UserPasswordHasher). Como se trata de un processor solo se usa para 
las operaciones de persistencia de datos (escritura). Dicho processor debemos vincularlo al  persist_processor que en doctrine es 
el encargado de realizar las operación de escritura en bd. Para ellos añadimos en el fichero services.yaml la siguiente configuración:

```yaml
        App\State\UserPasswordHasher:
        bind:
          $processor: '@api_platform.doctrine.orm.state.persist_processor'
```          

```php
final readonly class UserPasswordHasher implements ProcessorInterface
{
    public function __construct(
        private ProcessorInterface $processor,
        private UserPasswordHasherInterface $passwordHasher
    )
    {
    }

    /**
     * @param User $data
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): User | null
    {
        if (!$data->getPlainPassword()) {
            return $this->processor->process($data, $operation, $uriVariables, $context);
        }

        $hashedPassword = $this->passwordHasher->hashPassword(
            $data,
            $data->getPlainPassword()
        );
        $data->setPassword($hashedPassword);
        $data->eraseCredentials();

        return $this->processor->process($data, $operation, $uriVariables, $context);
    }
}
```

Nuestro processor obtiene el campo plainPassword y utiliza el passwordHasher (que hemos inyectado en el constructor de UserPasswordHasher) para
hashear la contraseña y guardarla en el campo password. Luego borramos el campo plainPassword.

A la entidad user le definimos donde debe usar el processor UserPasswordHasher así como grupos con el fin de especificar en que operaciones
se usan ciertos campos.

```php
#[ApiResource(
    operations: [
        new GetCollection(),
        new Post(validationContext: ['groups' => ['Default', 'user:create']], processor: UserPasswordHasher::class),
        new Get(),
        new Put(processor: UserPasswordHasher::class),
        new Patch(processor: UserPasswordHasher::class),
        new Delete(),
    ],
    normalizationContext: ['groups' => ['user:read']],
    denormalizationContext: ['groups' => ['user:create', 'user:update']],
)]
```

# JWT

Api platform usa LexikJWTAuthenticationBundle para la autenticación con JWT. Para configurar la autenticación con JWT seguimos
los pasos de https://api-platform.com/docs/core/jwt/

Luego simplemente llamamos a la ruta que hemos definido en el fichero routes.yaml y en el fichero security.yaml como 
json_login.check pasandole el email y la contraseña y en caso de validarlo nos devuelve el JSON.


