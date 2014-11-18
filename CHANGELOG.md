# Phony changelog

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

[Peridot]: https://github.com/peridot-php/peridot
[Pho]: https://github.com/danielstjules/pho
[SimpleTest]: https://github.com/lox/simpletest
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
