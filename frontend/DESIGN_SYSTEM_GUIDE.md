# Unified Design System Guide

This document outlines the unified design system implemented across all dashboards in the Attendance Management System to ensure consistent styling and user experience.

## Overview

The design system provides a comprehensive set of design tokens, components, and guidelines to ensure visual consistency across all role-based dashboards (Admin, Teacher, Student, Education).

## Design Tokens

### Color Palette

#### Primary Colors
- `--primary-50` to `--primary-900`: Blue color scale for primary actions and branding
- Usage: Primary buttons, active states, highlights

#### Semantic Colors
- `--success-*`: Green scale for positive actions and success states
- `--warning-*`: Amber scale for warnings and caution states  
- `--danger-*`: Red scale for errors and destructive actions
- `--info-*`: Blue scale for informational content

#### Neutral Colors
- `--slate-50` to `--slate-900`: Gray scale for text, borders, and backgrounds
- Usage: Text colors, borders, backgrounds, disabled states

### Typography

#### Font Sizes
- `--text-xs`: 12px (0.75rem) - Small text, labels
- `--text-sm`: 14px (0.875rem) - Secondary text, small buttons
- `--text-base`: 16px (1rem) - Body text, default
- `--text-lg`: 18px (1.125rem) - Large text, small headings
- `--text-xl`: 20px (1.25rem) - Medium headings
- `--text-2xl`: 24px (1.5rem) - Large headings
- `--text-3xl`: 30px (1.875rem) - Section headings
- `--text-4xl`: 36px (2.25rem) - Page titles

#### Font Weights
- `--font-thin`: 100
- `--font-light`: 300
- `--font-normal`: 400 (default)
- `--font-medium`: 500
- `--font-semibold`: 600
- `--font-bold`: 700
- `--font-extrabold`: 800
- `--font-black`: 900

### Spacing

#### Spacing Scale
- `--space-1`: 4px (0.25rem)
- `--space-2`: 8px (0.5rem)
- `--space-3`: 12px (0.75rem)
- `--space-4`: 16px (1rem) - Default padding
- `--space-5`: 20px (1.25rem)
- `--space-6`: 24px (1.5rem) - Card padding
- `--space-8`: 32px (2rem) - Section spacing
- `--space-10`: 40px (2.5rem)
- `--space-12`: 48px (3rem)
- `--space-16`: 64px (4rem) - Container padding

### Layout

#### Dimensions
- `--sidebar-width`: 256px (16rem) - Sidebar width
- `--header-height`: 64px (4rem) - Header height
- `--container-padding`: 32px (2rem) - Main content padding

#### Border Radius
- `--radius-sm`: 4px (0.25rem)
- `--radius-md`: 6px (0.375rem) - Default buttons/cards
- `--radius-lg`: 8px (0.5rem) - Cards, inputs
- `--radius-xl`: 12px (0.75rem) - Large components
- `--radius-2xl`: 16px (1rem) - Rounded components
- `--radius-3xl`: 24px (1.5rem) - Extra rounded

#### Shadows
- `--shadow-sm`: Subtle shadow for depth
- `--shadow-md`: Medium shadow for cards
- `--shadow-lg`: Large shadow for elevation
- `--shadow-xl`: Extra large shadow for modals
- `--shadow-2xl`: Maximum shadow for floating elements

## Components

### Unified Sidebar (`UnifiedSidebar.vue`)

**Purpose**: Consistent navigation across all dashboards

**Features**:
- Role-based navigation items
- User information display
- Account switching (for demo)
- Responsive design with mobile toggle
- Dark theme support

**Usage**:
```vue
<UnifiedSidebar 
  :current-module="currentModule"
  :user="user"
  :mock-users="mockUsers"
  @module-change="handleModuleChange"
  @user-change="handleUserChange"
  @logout="handleLogout"
/>
```

### Unified Header (`UnifiedHeader.vue`)

**Purpose**: Consistent top navigation with user controls

**Features**:
- Search functionality
- Theme toggle
- Notifications
- User menu with profile access
- Mobile responsive

**Usage**:
```vue
<UnifiedHeader 
  :user="user"
  :theme="theme"
  :notifications="notificationCount"
  @toggle-sidebar="toggleSidebar"
  @navigate="navigate"
  @logout="logout"
  @toggle-theme="toggleTheme"
/>
```

### Unified Card (`UnifiedCard.vue`)

**Purpose**: Consistent card layout for content sections

**Features**:
- Multiple variants (default, primary, success, warning, danger)
- Loading and error states
- Flexible header/body/footer slots
- Responsive padding options

**Usage**:
```vue
<UnifiedCard 
  title="Card Title"
  subtitle="Optional subtitle"
  variant="primary"
  size="md"
  :loading="isLoading"
  :error="errorMessage"
>
  <!-- Card content -->
</UnifiedCard>
```

### Buttons

**Classes**: `.btn`, `.btn-primary`, `.btn-secondary`, `.btn-success`, `.btn-warning`, `.btn-danger`, `.btn-ghost`

**Sizes**: `.btn-sm`, `.btn-md`, `.btn-lg`

**Usage**:
```html
<button class="btn btn-primary">Primary Action</button>
<button class="btn btn-secondary">Secondary Action</button>
<button class="btn btn-danger btn-sm">Delete</button>
```

### Inputs

**Class**: `.input`

**Features**:
- Consistent styling
- Focus states with primary color
- Disabled states
- Error states

**Usage**:
```html
<input type="text" class="input" placeholder="Enter text..." />
```

### Tables

**Class**: `.table`

**Features**:
- Consistent header styling
- Hover states
- Responsive design
- Dark theme support

**Usage**:
```html
<table class="table">
  <thead>
    <tr>
      <th>Column 1</th>
      <th>Column 2</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td>Data 1</td>
      <td>Data 2</td>
    </tr>
  </tbody>
</table>
```

### Badges

**Class**: `.badge`

**Variants**: `.badge-success`, `.badge-warning`, `.badge-danger`, `.badge-info`, `.badge-neutral`

**Usage**:
```html
<span class="badge badge-success">Active</span>
<span class="badge badge-warning">Pending</span>
<span class="badge badge-danger">Error</span>
```

## Layout Structure

### Main Layout

```html
<div id="app">
  <UnifiedSidebar />
  <div class="main-content">
    <UnifiedHeader />
    <!-- Page content -->
  </div>
</div>
```

### Content Structure

```html
<div class="main-content">
  <!-- Page header -->
  <div class="flex items-end justify-between gap-4 mb-8">
    <div>
      <h1 class="text-3xl font-bold">Page Title</h1>
      <p class="text-slate-600">Page description</p>
    </div>
    <!-- Actions -->
  </div>

  <!-- Content grid -->
  <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <UnifiedCard variant="primary">...</UnifiedCard>
    <UnifiedCard variant="success">...</UnifiedCard>
    <UnifiedCard variant="warning">...</UnifiedCard>
  </div>
</div>
```

## Responsive Design

### Breakpoints

- **Mobile**: `< 640px` - Single column layout
- **Tablet**: `640px - 1024px` - Two column layout
- **Desktop**: `> 1024px` - Three+ column layout

### Responsive Classes

- Use Tailwind's responsive prefixes: `md:`, `lg:`, `xl:`
- Example: `grid-cols-1 md:grid-cols-2 lg:grid-cols-3`

### Mobile Considerations

- Sidebar becomes hidden and toggleable
- Header search moves to full-width
- Cards stack vertically
- Font sizes adjust appropriately

## Dark Theme

### Enabling Dark Theme

Add `data-theme="dark"` to the root element:
```html
<html data-theme="dark">
```

### Theme-Aware Components

All unified components automatically support dark theme:
- Cards adjust background colors
- Text colors invert appropriately
- Borders and shadows adapt
- Inputs and buttons maintain contrast

## Accessibility

### Focus Management

- All interactive elements have visible focus states
- Use `:focus-visible` for keyboard navigation
- Maintain sufficient color contrast

### Semantic HTML

- Use proper heading hierarchy (h1, h2, h3...)
- Use semantic elements (nav, main, section, article)
- Provide alt text for images

### ARIA Labels

- Add `aria-label` for icon-only buttons
- Use `role="alert"` for error messages
- Implement proper form labels

## Cross-Browser Compatibility

### Supported Browsers

- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)

### CSS Features

- Use CSS custom properties with fallbacks
- Test flexbox and grid layouts
- Ensure proper box-sizing

### JavaScript

- Use ES6+ features with appropriate polyfills if needed
- Test async/await functionality
- Ensure proper error handling

## Implementation Guidelines

### 1. Use Unified Components

Always prefer unified components over custom implementations:
- Use `UnifiedSidebar` instead of custom sidebar
- Use `UnifiedCard` for content sections
- Use unified buttons and inputs

### 2. Follow Design Token Usage

Use CSS custom properties for consistent styling:
```css
/* Good */
color: var(--primary-600);
padding: var(--space-4);

/* Avoid */
color: #3b82f6;
padding: 16px;
```

### 3. Maintain Consistent Spacing

Use the spacing scale consistently:
```css
/* Good */
margin-bottom: var(--space-6);
padding: var(--space-4);

/* Avoid */
margin-bottom: 24px;
padding: 16px;
```

### 4. Implement Responsive Design

Always test across breakpoints:
- Mobile first approach
- Progressive enhancement
- Touch-friendly interactions

### 5. Ensure Accessibility

Follow accessibility best practices:
- Semantic HTML structure
- Proper focus management
- Sufficient color contrast
- Screen reader compatibility

### 6. Test Cross-Browser Compatibility

Test in all supported browsers:
- Layout consistency
- JavaScript functionality
- CSS animations and transitions
- Form interactions

## Migration Guide

### Updating Existing Components

1. **Replace custom sidebars** with `UnifiedSidebar`
2. **Replace custom headers** with `UnifiedHeader`
3. **Replace custom cards** with `UnifiedCard`
4. **Update button classes** to use unified button classes
5. **Update input styles** to use `.input` class
6. **Update table styles** to use `.table` class

### Updating CSS

1. **Replace hardcoded colors** with CSS custom properties
2. **Replace hardcoded spacing** with spacing variables
3. **Replace hardcoded fonts** with font variables
4. **Add dark theme support** where missing

### Testing

1. **Visual regression testing** across all dashboards
2. **Responsive testing** on all breakpoints
3. **Accessibility testing** with screen readers
4. **Cross-browser testing** on all supported browsers
5. **Performance testing** to ensure no regressions

## Troubleshooting

### Common Issues

1. **Inconsistent spacing**: Check if using design tokens
2. **Color inconsistencies**: Verify CSS custom properties
3. **Responsive issues**: Test all breakpoints
4. **Accessibility problems**: Use semantic HTML and ARIA labels
5. **Dark theme issues**: Ensure proper color inversion

### Debugging Tools

- Use browser dev tools to inspect computed styles
- Test with accessibility tools (axe, Lighthouse)
- Use responsive design testing tools
- Check CSS custom property values

## Conclusion

This unified design system ensures consistent styling across all dashboards while maintaining flexibility for role-specific requirements. By following these guidelines, developers can create cohesive, accessible, and maintainable interfaces that provide excellent user experience across all devices and themes.