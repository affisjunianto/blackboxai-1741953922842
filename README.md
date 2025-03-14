# Project Asset Structure

The project's assets have been organized into separate directories for better maintainability and easier template switching:

## Directory Structure

```
assets/
├── css/
│   └── style.css      # Main stylesheet containing all custom styles
└── js/
    └── main.js        # Main JavaScript file containing all custom scripts
```

## External Dependencies

### CSS
- Bootstrap 5.3.0
- Font Awesome 6.0.0-beta3
- DataTables Bootstrap 5
- Select2 4.1.0
- SweetAlert2

### JavaScript
- jQuery 3.6.0
- Bootstrap Bundle 5.3.0 (includes Popper)
- DataTables 1.11.5
- Select2 4.1.0
- SweetAlert2

## Usage

The CSS and JavaScript files are included in the following files:
- `includes/header.php`: Contains CSS imports
- `includes/footer.php`: Contains JavaScript imports

To switch templates:
1. Create a new CSS file in the `assets/css` directory
2. Update the CSS file reference in `includes/header.php`
3. If needed, create a new JavaScript file in `assets/js` directory
4. Update the JavaScript file reference in `includes/footer.php`