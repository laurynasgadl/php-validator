# Changelog

## [3.0.0] - 2023-07-19
### Updated
- Updated dependencies to support PHP 8.0

## [2.3.0] - 2022-02-08
### Updated
- Changed PHP sort functions to return `1`, `0` or `-1` instead of boolean

## [2.2.7] - 2021-03-19
### Updated
- Fixed Helper test namespace not matching PSR-0/PSR-4
- Fixed `setUp` visibility override in DefaultRuleTest

## [2.2.6] - 2020-10-14
### Updated
- String rule now does not fail on numerical input

## [2.2.5] - 2020-05-28
### Updated
- Required rule now does not fail on empty string

## [2.2.4] - 2020-05-27
### Added
- `validated` method for getting only the validated params of the last validation

## [2.2.3] - 2020-05-20
### Added
- Email rule
- URL rule
- IP rule

## [2.2.2] - 2020-05-19
### Added
- AlphaDash rule
- AlphaNumeric rule
- Regex rule

## [2.2.1] - 2020-05-12
### Updated
- Required rule now fails on empty string

## [2.2.0] - 2020-05-12
### Added
- Default rule

## [2.1.0] - 2020-05-08
### Added
- Ability to register custom rules so that they can be resolved via string

## [2.0.1] - 2020-05-08
### Updated
- Default validation fail message formatting

## [2.0.0] - 2020-05-08
### Added
- Custom validation messages
- RequiredWith rule
### Updated
- Validator rule validation flow
### Removed
- Validator error bag (validation fail will throw exception immediately)

## [1.1.6] - 2020-05-06
### Fixed
- RequiredWithout continuing validation on null

## [1.1.5] - 2020-05-06
### Added
- Numeric rule

## [1.1.4] - 2019-11-29
### Added
- RequiredWithout rule

## [1.1.3] - 2019-11-22
### Fixed
- Required nested params being validated in a non-required array

## [1.1.2] - 2019-11-22
### Updated
- Validation error bag display
### Fixed
- Nested wildcard param routing

## [1.1.1] - 2019-11-22
### Added
- Ability to pass default context handler to constructor
### Fixed
- Empty array of params not being validated

## [1.1.0] - 2019-10-04
### Added
- Wildcard support

## [1.0.1] - 2019-10-22
### Added
- Boolean rule
### Updated
- Default `Context` handler package
- README.md

## [1.0.0] - 2019-10-21
### Added
- Initial release
 
 
___
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).
