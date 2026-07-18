---
name: Digestive Wellness Interface
colors:
  surface: '#f7f9fb'
  surface-dim: '#d8dadc'
  surface-bright: '#f7f9fb'
  surface-container-lowest: '#ffffff'
  surface-container-low: '#f2f4f6'
  surface-container: '#eceef0'
  surface-container-high: '#e6e8ea'
  surface-container-highest: '#e0e3e5'
  on-surface: '#191c1e'
  on-surface-variant: '#414754'
  inverse-surface: '#2d3133'
  inverse-on-surface: '#eff1f3'
  outline: '#717786'
  outline-variant: '#c1c6d7'
  surface-tint: '#005bc0'
  primary: '#0059bb'
  on-primary: '#ffffff'
  primary-container: '#0070ea'
  on-primary-container: '#fefcff'
  inverse-primary: '#adc7ff'
  secondary: '#006d37'
  on-secondary: '#ffffff'
  secondary-container: '#6bfe9c'
  on-secondary-container: '#00743a'
  tertiary: '#4d5f5d'
  on-tertiary: '#ffffff'
  tertiary-container: '#667876'
  on-tertiary-container: '#f3fffd'
  error: '#ba1a1a'
  on-error: '#ffffff'
  error-container: '#ffdad6'
  on-error-container: '#93000a'
  primary-fixed: '#d8e2ff'
  primary-fixed-dim: '#adc7ff'
  on-primary-fixed: '#001a41'
  on-primary-fixed-variant: '#004493'
  secondary-fixed: '#6bfe9c'
  secondary-fixed-dim: '#4ae183'
  on-secondary-fixed: '#00210c'
  on-secondary-fixed-variant: '#005228'
  tertiary-fixed: '#d3e7e4'
  tertiary-fixed-dim: '#b7cac8'
  on-tertiary-fixed: '#0d1e1d'
  on-tertiary-fixed-variant: '#384a48'
  background: '#f7f9fb'
  on-background: '#191c1e'
  surface-variant: '#e0e3e5'
typography:
  headline-xl:
    fontFamily: Manrope
    fontSize: 48px
    fontWeight: '700'
    lineHeight: '1.2'
  headline-lg:
    fontFamily: Manrope
    fontSize: 32px
    fontWeight: '600'
    lineHeight: '1.3'
  headline-md:
    fontFamily: Manrope
    fontSize: 24px
    fontWeight: '600'
    lineHeight: '1.4'
  body-lg:
    fontFamily: Inter
    fontSize: 18px
    fontWeight: '400'
    lineHeight: '1.6'
  body-md:
    fontFamily: Inter
    fontSize: 16px
    fontWeight: '400'
    lineHeight: '1.6'
  label-sm:
    fontFamily: Inter
    fontSize: 14px
    fontWeight: '500'
    lineHeight: '1.2'
    letterSpacing: 0.02em
rounded:
  sm: 0.25rem
  DEFAULT: 0.5rem
  md: 0.75rem
  lg: 1rem
  xl: 1.5rem
  full: 9999px
spacing:
  unit: 8px
  container-max: 1280px
  gutter: 24px
  margin: 64px
  section-gap: 120px
---

## Brand & Style

The design system is centered on the concept of "Clinical Serenity." Given the sensitive nature of stomach health analysis, the UI must act as a calming presence, reducing user anxiety through clarity and openness. The brand personality is empathetic, authoritative, and meticulously organized.

The chosen style is **Minimalism** with a touch of **Soft-Modernism**. It prioritizes heavy whitespace to prevent information overload and uses subtle depth to guide the eye. Every element is designed to feel "breathable," ensuring that medical data and educational content are easily digestible. The aesthetic avoids cold, sterile medical tropes in favor of a warm, human-centric healthcare experience.

## Colors

The color palette is engineered to evoke trust and physical well-being. 
- **Primary (Healing Blue):** A deep, reliable blue used for primary actions and brand presence. It signals professionalism and stability.
- **Secondary (Vital Mint):** A fresh, organic green used for health indicators, positive analysis results, and "success" states. It represents digestion and vitality.
- **Tertiary (Soft Cyan):** A very pale wash used for background sections and subtle UI accents to keep the interface feeling light.
- **Neutral (Slate Grays):** A range of cool-toned grays are used for text and borders to maintain a sophisticated, clean look without the harshness of pure black.

## Typography

This design system utilizes a dual-font approach to balance personality with high-performance readability. 
- **Manrope** is used for all headlines. Its geometric yet soft curves provide a modern, friendly character that feels premium and approachable.
- **Inter** is used for all body copy and UI labels. As a highly functional sans-serif, it ensures maximum legibility for long-form educational content and complex health data.

Line heights are intentionally generous (1.6x for body) to improve reading stamina and reduce cognitive load for users who may be stressed about their health results.

## Layout & Spacing

The layout follows a **Fixed Grid** model for the desktop experience, centering content within a 1280px container to ensure optimal line lengths for reading. 

The spacing rhythm is based on an 8px linear scale. A defining characteristic of this design system is the use of "Extreme Whitespace"—using larger gaps (120px+) between major sections to provide visual breathing room. Information is grouped into logical clusters using ample padding (32px-48px) within cards to avoid a cluttered or "cramped" feeling.

## Elevation & Depth

Visual hierarchy is established through **Ambient Shadows** and **Tonal Layering**. Instead of heavy borders, the design system uses very soft, diffused shadows with a slight blue tint (`rgba(0, 123, 255, 0.05)`) to lift cards off the neutral background.

- **Level 0 (Background):** The base neutral color (#F8FAFC).
- **Level 1 (Cards/Surface):** Pure white (#FFFFFF) with a soft 16px blur shadow.
- **Level 2 (Interactive/Floating):** Higher elevation with a 32px blur shadow, used for tooltips or active selection states.

This creates a "pillowy" depth that feels gentle and inviting rather than sharp or aggressive.

## Shapes

The shape language is consistently **Rounded**. Sharp corners are avoided to maintain a "soft" medical vibe. Standard UI components like buttons and input fields use a 0.5rem (8px) radius. Larger containers, such as content cards and analysis modules, use a 1rem (16px) or 1.5rem (24px) radius to emphasize a friendly, non-intimidating structure. 

Iconography should follow this logic, using rounded terminals and thick, consistent stroke weights.

## Components

- **Buttons:** Primary buttons use a solid blue fill with white text. Secondary buttons use a mint green ghost style (border only). Transitions should be smooth (200ms) to maintain the calming effect.
- **Input Fields:** Large tap targets with subtle 1px light gray borders. On focus, the border transitions to primary blue with a soft outer glow.
- **Cards:** The primary vehicle for health data. They feature white backgrounds, generous internal padding, and soft rounded corners.
- **Health Chips:** Small, pill-shaped indicators used for tagging symptoms or food types. They use low-saturation background tints of the secondary color.
- **Progress Trackers:** Used for multi-step health analyses. These should use "soft-step" indicators—rounded lines rather than dots to feel more fluid.
- **Data Visualizations:** Charts should utilize the Mint Green and Light Blue palette exclusively, using rounded ends on bar charts and smooth tension on line graphs to avoid "spiky," alarming visuals.