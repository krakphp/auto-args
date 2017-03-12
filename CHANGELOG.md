# Change Log
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this project adheres to [Semantic Versioning](http://semver.org/).

## [Unreleased]

## [0.2.2] - 2017-03-11
### Fixed

- Bug in auto wiring behavior so that subclassOf middleware runs before the container middleware
- Updated composer deps to allow 0.4 and 0.5 version of the mw package.

## [0.2.1] - 2017-02-26

### Fixed

- Bug in `AutoArgs::construct` if the object does not have a constructor.

## [0.2.0] - 2017-02-17

### Changed

- pimpleContainer to container interop
- Removed callable typehints to allow for constructor argument resolving
- Added new `construct` method to allow object construction

## [0.1.0] - 2017-01-14
### Added

- Initial Implementation
- Added Documentation
