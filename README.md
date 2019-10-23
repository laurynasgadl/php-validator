# PHP-Validator
_inspired by Laravel's form validation_

```php
use Luur\Validator\Validator;
use Luur\Validator\Rules\Concrete\MinRule;
use Luur\Validator\Rules\Concrete\RequiredRule;
use Luur\Validator\Exceptions\ValidationFailed;

$validator = new Validator();

$rules = [
    'client_id' => 'required|integer',
    'amount' => [new RequiredRule(), new MinRule(0)],
    'count' => 'between:-12,50',
    'details' => 'array|required',
    'details.name' => 'string|required',
];

$params = [
    'client_id' => 12345,
    'amount' => 1234,
    'details' => [
        'name' => 'John Doe',
    ],
];

try {
    $validator->validate($rules, $params);
catch (ValidationFailed:: $exception) {
    var_dump($validator->getErrors());
}
```

# Installation

`composer require laurynasgadl/php-validator`

# Documentation
## Rules
#### Custom rules

```php
use Luur\Validator\Validator;

class CustomRule extends AbstractRule {
    /**
     * @param mixed $value
     * @return bool
     */
    public function passes($value)
    {
        return true;
    }
}

$validator = new Validator();

$params = ['test' => 123];
$result = $validator->validate([
    'test' => new CustomRule(),
], $params);
```

#### Existing rules
`array` : the value needs to be an array

`between:0,10` : the value needs to be between the given range

`float` : the value needs to be a float

`integer` : the value needs to be an integer

`max:100` : the value needs to be less or equal to the given amount

`min:100` : the value needs to be greater or equal to the given amount

`required` : the parameter needs to exist in the data set

`size` : the size of the value needs to be equal to the given amount. The size of a string is its length, the size of an array is the number of elements inside it, the size of a boolean is 0 or 1.

`string` : the value needs to be a string

`boolean` : the value needs to be a boolean
