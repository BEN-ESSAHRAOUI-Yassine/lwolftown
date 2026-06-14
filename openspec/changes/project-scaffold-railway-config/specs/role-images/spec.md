## ADDED Requirements

### Requirement: Role image directory structure
The system SHALL create public/images/roles/ directory with a placeholder.svg file for roles without custom images.

#### Scenario: Directory exists
- **WHEN** the project is scaffolded
- **THEN** public/images/roles/ directory exists

#### Scenario: Placeholder SVG exists
- **WHEN** the project is scaffolded
- **THEN** public/images/roles/placeholder.svg exists

### Requirement: Placeholder SVG specifications
The system SHALL include a placeholder.svg that is a generic atmospheric humanoid silhouette matching the role card dimensions.

#### Scenario: Correct viewBox
- **WHEN** placeholder.svg is parsed
- **THEN** viewBox is `0 0 400 560`

#### Scenario: Correct stroke color
- **WHEN** placeholder.svg is rendered
- **THEN** stroke color is #C8922A (accent-warm)

#### Scenario: No fill
- **WHEN** placeholder.svg is rendered
- **THEN** the silhouette has no fill (stroke only on transparent background)

#### Scenario: Humanoid silhouette shape
- **WHEN** placeholder.svg is rendered
- **THEN** it shows a humanoid silhouette shape suitable for a role card

### Requirement: Role image file convention
The system SHALL support role images at public/images/roles/{role_key}.png (400x560) and optional @2x variant at public/images/roles/{role_key}@2x.png (800x1120).

#### Scenario: Standard image path
- **WHEN** a role image exists at public/images/roles/seer.png
- **THEN** the Blade helper resolves it as the role image source

#### Scenario: Retina image path
- **WHEN** a role image exists at public/images/roles/seer@2x.png
- **THEN** the Blade helper includes it in the srcset as 2x

#### Scenario: No image falls back to placeholder
- **WHEN** no PNG exists for a role
- **THEN** the Blade helper uses public/images/roles/placeholder.svg as the src

#### Scenario: @2x missing falls back to @1x
- **WHEN** only seer.png exists (no @2x)
- **THEN** the img src is seer.png and no srcset 2x entry is included

### Requirement: Role image Blade helper
The system SHALL provide Blade helper logic that resolves role image src and srcset based on file existence.

#### Scenario: Both resolutions exist
- **WHEN** seer.png and seer@2x.png both exist
- **THEN** the img tag has src="seer.png" and srcset="seer.png 1x, seer@2x.png 2x"

#### Scenario: Only 1x exists
- **WHEN** only seer.png exists
- **THEN** the img tag has src="seer.png" and srcset="seer.png 1x"

#### Scenario: Neither exists
- **WHEN** no PNG files exist for the role
- **THEN** the img tag has src="placeholder.svg" and no srcset attribute

### Requirement: Role image card layout
The system SHALL render role images in the revealed state of the role card with a dark gradient overlay and text content at the bottom.

#### Scenario: Image covers card
- **WHEN** a role card is in revealed state
- **THEN** the role image uses object-fit: cover as full card background

#### Scenario: Gradient overlay present
- **WHEN** a role card is in revealed state
- **THEN** a dark gradient overlay (linear-gradient to top, rgba(0,0,0,0.92) 40% to rgba(0,0,0,0.3) 100%) covers the image

#### Scenario: Text content overlaid
- **WHEN** a role card is in revealed state
- **THEN** role name (Cinzel), faction label, night order, and abilities text appear at the bottom over the gradient

#### Scenario: Masked state hides image
- **WHEN** a role card is in masked state
- **THEN** no role image is shown — only the atmospheric card face
