# PHP-Validator
_inspired by Laravel's form validation_

```php
use Luur\Validator\Validator;
use Luur\Validator\Rules\Concrete\MinRule;
use Luur\Validator\Rules\Concrete\RequiredRule;
use Luur\Validator\Exceptions\ValidationFailed;

$validator = new Validator();

$rules = [
    'client_id'    => 'required|integer',
    'amount'       => [new RequiredRule(), new MinRule(0)],
    'count'        => 'between:-12,50',
    'details'      => 'array|required',
    '*.name'       => 'string|required',
];

$params = [
    'client_id' => 12345,
    'amount'    => 1234,
    'details'   => [
        'name'  => 'John Doe',
    ],
];

try {
    $validator->validate($rules, $params);
} catch (ValidationFailed $exception) {
    var_dump($exception->getMessage());
}
```

# Installation

`composer require laurynasgadl/php-validator`

# Documentation
## Rules
#### Custom rules

```php
use Luur\Validator\Rules\AbstractRule;
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

You can also register the rule to be able to use it as a string:

```php
use Luur\Validator\Rules\AbstractRule;
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
$validator->registerRule(CustomRule::getSlug(), CustomRule::class);

$params = ['test' => 123];
$result = $validator->validate([
    'test' => 'test',
], $params);
```

#### Existing rules
`array` : the value needs to be an array

`between:{from_size},{to_size}` : the value needs to be between the given range

`float` : the value needs to be a float

`integer` : the value needs to be an integer

`max:{max_size}` : the value needs to be less or equal to the given amount

`min:{min_size}` : the value needs to be greater or equal to the given amount

`required` : the parameter needs to exist in the data set

`required_with:{param_1}...` : the parameter needs to exist in the data set if all the other parameters exist

`required_without:{param_1}...` : the parameter needs to exist in the data set if one of the other parameters do not exist

`size` : the size of the value needs to be equal to the given amount. The size of a string is its length, the size of an array is the number of elements inside it, the size of a boolean is 0 or 1.

`string` : the value needs to be a string

`boolean` : the value needs to be a boolean

`numeric` : the value needs to be a numeric

`default:{value}` : the rule will always be applied first, meaning, params can successfully pass `required` rules even if their value is not set or is null

`alpha_dash` : the value can only be made up of uppercase and lowercase letters, numbers and `-` or `_` symbols

`alpha_numeric` : the value can only be made up of uppercase and lowercase letters and numbers

`regex:{pattern}` : the value needs to match the given pattern

`email` : the value needs to be an email

`url` : the value needs to start with `http://` or `https://` and only contain URL-valid symbols

`ip` : the value needs to be an IP address

## Custom messages
You can set custom validation messages either via the Validator constructor or the `validate` method:
```php
use Luur\Validator\Validator;

$validator = new Validator();

$rules = [
    'options.*.key' => 'string|required',
];

$params = [
    'options' => [
        [
            'key' => 'passes',
            'value' => true,
        ],
        [
            'key' => ['passes'],
            'value' => false,
        ],
    ],
];

$messages = [
    'options.*.key.string' => 'Option key should be a string',
];

$validator->validate($rules, $params, $messages);
```
