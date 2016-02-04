# Phony changelog

## Next release

- **[BC BREAK]** Mocking functions now accept ad hoc definitions in the `$types`
  argument, hence the `$definition` argument has been removed from `mock()`,
  `partialMock()`, and `mockBuilder()` ([#117]).
- **[BC BREAK]** Mocking functions `mock()`, `partialMock()`, and
  `mockBuilder()` no longer accept the `$className` argument. Custom class names
  can still be used via `named()` on mock builders ([#117]).
- **[BC BREAK]** Mocking functions `mock()`, `partialMock()`, and
  `mockBuilder()` no longer accept reflection classes or mock builders in the
  `$types` argument ([#117]).
- **[BC BREAK]** Mock definition values can no longer be generic objects
  ([#117]).
- **[BC BREAK]** Removed the `$useGeneratorSpies` and `$useTraversableSpies`
  argument from both `spy()` and `stub()` ([#123]).
- **[BC BREAK]** Removed the `$self` and `$defaultAnswerCallback` arguments from
  `stub()` ([#123]).
- **[BC BREAK]** Rewrite and rename of mock builder API methods for creating
  mocks ([#103]).
- **[NEW]** Mock builders can now be copied via the `clone` operator ([#101]).
- **[IMPROVED]** Made API setter style methods fluent ([#98]).
- **[IMPROVED]** Instance handles are automatically adapted when stubbing and
  verifying ([#126]).
- **[IMPROVED]** Added checks for unused stub criteria ([#126]).
- **[IMPROVED]** Default answer callbacks are now a first-class concept for
  mocks ([#90]).
- **[FIXED]** Fixed bug when mocking traits with magic call methods ([#127]).
- **[FIXED]** Mocking, and calling of return-by-reference methods no longer
  causes errors to be emitted ([#105]).
- **[FIXED]** Ad-hoc mocks that differ only by function body no longer result in
  re-use of the same mock class ([#131]).

[#90]: https://github.com/eloquent/phony/issues/90
[#98]: https://github.com/eloquent/phony/issues/98
[#101]: https://github.com/eloquent/phony/issues/101
[#103]: https://github.com/eloquent/phony/issues/103
[#105]: https://github.com/eloquent/phony/issues/105
[#117]: https://github.com/eloquent/phony/issues/117
[#123]: https://github.com/eloquent/phony/issues/123
[#126]: https://github.com/eloquent/phony/issues/126
[#127]: https://github.com/eloquent/phony/issues/127
[#131]: https://github.com/eloquent/phony/issues/131

## 0.7.0 (2015-12-20)

- **[NEW]** Implemented `firstCall()` and `lastCall()` ([#93]).
- **[IMPROVED]** Support for PHP 7 engine error exceptions ([#119]).
- **[IMPROVED]** Support for PHP 7 scalar type hints ([#106] - thanks
  [@jmalloc]).
- **[IMPROVED]** Support for PHP 7 return type declarations ([#104], [#108] -
  thanks [@jmalloc]).
- **[IMPROVED]** Support for PHP 7 methods names that match tokens ([#110] -
  thanks [@jmalloc]).
- **[IMPROVED]** Partial support for PHP 7 generator returns ([#111] - thanks
  [@jmalloc]).
- **[IMPROVED]** Tidied up many interfaces and doc blocks.

[#93]: https://github.com/eloquent/phony/issues/93
[#104]: https://github.com/eloquent/phony/issues/104
[#106]: https://github.com/eloquent/phony/issues/106
[#108]: https://github.com/eloquent/phony/issues/108
[#110]: https://github.com/eloquent/phony/issues/110
[#111]: https://github.com/eloquent/phony/issues/111
[#119]: https://github.com/eloquent/phony/issues/119

## 0.6.4 (2015-12-19)

- **[FIXED]** Simplified method resolution rules. Fixes issue when mocking
  interfaces and traits ([#112]).

[#112]: https://github.com/eloquent/phony/issues/112

## 0.6.3 (2015-12-18)

- **[FIXED]** Fixed custom mocks with invocable objects as method
  implementations ([#113]).
- **[FIXED]** Fixed required, but nullable parameters in function signatures
  ([#114]).

[#113]: https://github.com/eloquent/phony/issues/113
[#114]: https://github.com/eloquent/phony/issues/114

## 0.6.2 (2015-12-16)

- **[IMPROVED]** Huge additions to the documentation ([#85], [#88]).
- **[FIXED]** Fixed error with EqualToMatcher when comparing object to
  non-object (#100).

[#85]: https://github.com/eloquent/phony/issues/85
[#88]: https://github.com/eloquent/phony/issues/88
[#100]: https://github.com/eloquent/phony/issues/100

## 0.6.1 (2015-11-16)

- **[IMPROVED]** Mock instances labels are now compared by the equal to matcher
  (#91).
- **[IMPROVED]** The inline exporter now outputs mock labels ([#91]).

[#91]: https://github.com/eloquent/phony/issues/91

## 0.6.0 (2015-11-12)

- **[NEW]** Support for stub default answer callbacks.
- **[FIXED]** Fixed full mock default answer bug (#89).

[#89]: https://github.com/eloquent/phony/issues/89

## 0.5.2 (2015-11-05)

- **[FIXED]** Fixed stripping of exception xdebug message in exporter and equal
  to matcher ([#87]).
- **[DOCUMENTATION]** Added documentation.

[#87]: https://github.com/eloquent/phony/pull/87

## 0.5.1 (2015-10-22)

- **[IMPROVED]** Prevent exporter and matcher from traversing into mock
  internals ([#82]).
- **[FIXED]** Fixed assertion recording bug with `noInteraction()` ([#83]).

[#82]: https://github.com/eloquent/phony/issues/82
[#83]: https://github.com/eloquent/phony/issues/83

## 0.5.0 (2015-10-20)

- **[BC BREAK]** Removed `fullMock()`, changed `mock()` to create full mocks,
  and added `partialMock()` for creating partial mocks ([#73]).

[#73]: https://github.com/eloquent/phony/issues/73

## 0.4.0 (2015-10-20)

- **[IMPROVED]** Implemented new 'equal to' matcher ([#70] - thanks [@jmalloc]).
- **[IMPROVED]** Improved rendering of assertion failure messages ([#71]).
- **[IMPROVED]** String messages now allowed by `throws()` ([#76]).
- **[FIXED]** Fixed magic method mocking bug ([#74]).
- **[FIXED]** Fixed mocking of exceptions under HHVM ([#75]).
- **[FIXED]** Attempting to stub a final method now throws an exception ([#77]).

[#70]: https://github.com/eloquent/phony/issues/70
[#71]: https://github.com/eloquent/phony/issues/71
[#74]: https://github.com/eloquent/phony/issues/74
[#75]: https://github.com/eloquent/phony/issues/75
[#76]: https://github.com/eloquent/phony/issues/76
[#77]: https://github.com/eloquent/phony/issues/77

## 0.3.0 (2015-07-22)

- **[NEW]** PHP 7 support.
- **[NEW]** Support for variadic functions ([#64] - thanks [@jmalloc]).
- **[NEW]** Implemented `eventAt()` and `callAt()` for verification results
  ([#17]).
- **[NEW]** Implemented `Call::argument()` ([#56]).
- **[NEW]** Implemented `MockBuilder::source()` for easier debugging ([#45]).
- **[NEW]** Implemented `anyOrder()` ([#60]).
- **[IMPROVED]** Vast improvements to verification failure output ([#66]).
- **[IMPROVED]** Allow use of phonySelf parameter everywhere ([#63]).
- **[IMPROVED]** Optimizations to the matcher driver system ([#67]).
- **[IMPROVED]** Optimizations to the equal to matcher ([#69]).
- **[IMPROVED]** Calls to eval() no longer use @ suppression.

[#17]: https://github.com/eloquent/phony/issues/17
[#45]: https://github.com/eloquent/phony/issues/45
[#56]: https://github.com/eloquent/phony/issues/56
[#60]: https://github.com/eloquent/phony/issues/60
[#63]: https://github.com/eloquent/phony/issues/63
[#64]: https://github.com/eloquent/phony/pull/64
[#66]: https://github.com/eloquent/phony/issues/66
[#67]: https://github.com/eloquent/phony/issues/67
[#69]: https://github.com/eloquent/phony/issues/69

## 0.2.1 (2015-02-28)

- **[FIXED]** Cardinality checks for `received()` now work as expected ([#54]).
- **[FIXED]** Methods names are correctly treated as case-insensitive ([#58]).
- **[FIXED]** Can mock an interface that extends `Traversable` ([#59]).
- **[FIXED]** Calling of trait constructors ([#61]).

[#54]: https://github.com/eloquent/phony/issues/54
[#58]: https://github.com/eloquent/phony/issues/58
[#59]: https://github.com/eloquent/phony/issues/59
[#61]: https://github.com/eloquent/phony/issues/61

## 0.2.0 (2014-11-18)

- **[BC BREAK]** Renamed IDs to labels.
- **[NEW]** Verify no interaction ([#27]).
- **[NEW]** Manual constructor calling ([#36], [#46]).
- **[NEW]** Setters for labels ([#35]).
- **[NEW]** [Peridot] integration ([#50]).
- **[NEW]** [Pho] integration ([#51]).
- **[NEW]** [SimpleTest] integration ([#20]).
- **[FIXED]** Trait mocking is now working ([#42], [#49]).
- **[FIXED]** Stubbing interface bugs ([#53]).
- **[IMPROVED]** Better assertion messages ([#41]).
- **[IMPROVED]** Generator spies under HHVM ([#29]).
- **[IMPROVED]** Better mock definition equality checking ([#47]).
- **[IMPROVED]** Throw an exception when passing the wrong types to `inOrder()`
  ([#52]).
- **[IMPROVED]** Magic 'self' parameter detection ([#48]).

[peridot]: https://github.com/peridot-php/peridot
[pho]: https://github.com/danielstjules/pho
[simpletest]: https://github.com/lox/simpletest
[#20]: https://github.com/eloquent/phony/issues/20
[#27]: https://github.com/eloquent/phony/issues/27
[#29]: https://github.com/eloquent/phony/issues/29
[#35]: https://github.com/eloquent/phony/issues/35
[#36]: https://github.com/eloquent/phony/issues/36
[#41]: https://github.com/eloquent/phony/issues/41
[#42]: https://github.com/eloquent/phony/issues/42
[#46]: https://github.com/eloquent/phony/issues/46
[#47]: https://github.com/eloquent/phony/issues/47
[#48]: https://github.com/eloquent/phony/issues/48
[#49]: https://github.com/eloquent/phony/issues/49
[#50]: https://github.com/eloquent/phony/issues/50
[#51]: https://github.com/eloquent/phony/issues/51
[#52]: https://github.com/eloquent/phony/issues/52
[#53]: https://github.com/eloquent/phony/issues/53

## 0.1.1 (2014-10-26)

- **[IMPROVED]** Performance improvements when repeatedly mocking the same
  types ([#44]).
- **[IMPROVED]** Performance improvements when mocking large classes ([#44]).
- **[IMPROVED]** Improved exception message when mocking an undefined type
  ([#40]).

[#40]: https://github.com/eloquent/phony/issues/40
[#44]: https://github.com/eloquent/phony/issues/44

## 0.1.0 (2014-10-21)

- **[NEW]** Initial implementation.

[@jmalloc]: https://github.com/jmalloc
