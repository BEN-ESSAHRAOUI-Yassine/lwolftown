## MODIFIED Requirements

### Requirement: AppServiceProvider boot
The system SHALL configure AppServiceProvider boot() with CarbonImmutable date class, locale from session, session-token auth guard registration, and DB prohibitDestructiveCommands in production.

#### Scenario: CarbonImmutable as default date class
- **WHEN** AppServiceProvider boot() runs
- **THEN** Carbon::use(CarbonImmutable::class) is called

#### Scenario: Locale set from session
- **WHEN** session contains locale='fr'
- **THEN** app()->locale is set to 'fr'

#### Scenario: Locale defaults to en when absent
- **WHEN** session has no locale key
- **THEN** app()->locale remains 'en'

#### Scenario: Auth guard registered
- **WHEN** AppServiceProvider boot() runs
- **THEN** auth()->viaRequest('session-token', closure) is registered that resolves Player from cookie

#### Scenario: Destructive commands blocked in production
- **WHEN** app environment is 'production'
- **THEN** DB::prohibitDestructiveCommands() is called
