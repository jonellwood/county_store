# Add Product System

A modern Vue 3 application for adding new products to the County Store admin system.

## Features

- **Professional UI**: Clean, modern interface with professional styling
- **Vue 3 Integration**: Reactive form handling via CDN
- **Color Management**: Visual color selection with hex preview
- **Size & Pricing**: Dynamic size selection with individual pricing
- **Form Validation**: Client-side and server-side validation
- **Database Integration**: Proper transactional database operations
- **Responsive Design**: Works on desktop and mobile devices

## File Structure

```
/admin/pages/add-product/
├── index.php              # Main Vue 3 application
├── add-product.css        # Professional styling
├── api/
│   ├── get-options.php    # Fetch colors, sizes, product types
│   └── add-product.php    # Handle product creation
└── README.md              # This file
```

## Database Operations

The system handles complex product relationships:

1. **products_new**: Main product information
2. **products_colors**: Product-to-color relationships
3. **products_sizes_new**: Product-to-size relationships  
4. **prices**: Size-specific pricing with vendor information

All operations use database transactions to ensure data integrity.

## Usage

1. Navigate to `/admin/pages/add-product/`
2. Fill in basic product information
3. Select available colors (visual color picker)
4. Add sizes with individual pricing
5. Submit form to save product

## API Endpoints

- `GET api/get-options.php` - Fetch all available colors, sizes, and product types
- `POST api/add-product.php` - Create new product with all relationships

## Validation

- Required fields: Product code, name, product type, at least one color, at least one size with price
- Product code uniqueness checking
- Price validation (must be > 0)
- Database constraint validation

## Navigation

- Link added to edit-products page header
- Back navigation to product list
- Can be integrated into main admin navigation

## Styling Philosophy

The CSS uses a professional design system with:

- Custom CSS variables for consistent theming
- No external frameworks (Bootstrap/Tailwind)
- Reusable component patterns
- Responsive design principles
- Accessibility considerations

This styling approach can be replicated across other admin routes for consistency.
