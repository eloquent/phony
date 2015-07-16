# Phony changelog

## Next release

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
