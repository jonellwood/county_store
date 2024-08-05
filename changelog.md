# County Store Application Changelog

## Version 2.0.0 - (2024-07-12)

## **🎊 UI Updates 🎉**

- UI has been updated to reflect changes made to the database structure.
- Users will see the price for each size available without having to choose the size first.
- A summary of the item to include tax and fees is presented to the user on the right and includes the total value of the current cart.
- Items placed in the cart, but not purchased are written to local storage in the browser. Cart items should persist beyond the server session.
  - Lots of caveats and disclaimers with this one. It's local storage in the browser, not a bank vault.
- View Filters are back!! Users can use the filter option to remove from the current view items they want to see.
- Top menu has been updated for ease of use. Boots and Hats no longer have a sub menu also called boots and hats.(Seems like an easy win for everyone)
- View Cart has been redesigned to provide more clarity to the user for each item as well as the total cost.
- Items in the cart can be edited from the view Cart page. Users can update the size, color, quantity, logo, or department name placement right in the cart.
- Added reset button of sorts to filters popover

## Version 1.2.7 - (2024-06-26)

### **Bug Squished**

- Fixed an issue where "Order All" function would not order items in "updated" status.
- Added markdownlint.json to improve formatting for this file as well as linting warnings.

## Version 1.2.6 - (2024-06-18)

### **Bug Squished**

- Fixed an issue causing certain item with the same options to not show up on vendor order report.

## Version 1.2.5 - (2024-05-31) &#129382;

### **Bug Squished**

- We're doing more than chopping broccoli today.
  - Fixed an issue in "Vendor Reports" that caused item received into inventory to not display correctly.

### **UI Updates**

- Format updates to the vendor report added additional information to make the report more useful.
  - &#9215; Added status to show if an item has already been received.
  - Added Order ID value and Order Details ID value to easier reference.
  - &#128247; Logo Image is updated with "Department Name" under logo IF request wants the department name under the logo. If the user has selected "No Department Name" the logo image is updated to reflect that.
- Changed the front page to load 4 random products rather than the top 4 because I am sick and tired of looking at the same 4 products.
- &#127882; "View Cart" page images now reflect the color the user selected and added to cart! &#129395;

## Version 1.2.4 - (2024-05-28) &#128295;

### **Update**

- "Employee Requests" page UI updated:
  - Orders in the "Ordered Status" can now **ONLY** be received by Approvers. All other changes must go through the Help Desk.
  - Individual items can be received by clicking on the specific line item, similar to approving and denying line items.
  - When in the "Ordered" status, the button for "Approve or Deny All" is replaced with a "Receive All" option to allow the user to receive all items in that order at once. &#127881; - **Use this power wisely**

## Version 1.2.3 - (2024-05-15) &#128545;

### **Fix**

- Fixed this view. Markdown parser library just quit - implemented a new one which is much faster.
- Updated footer to conform to BCIT standards with link to changelog containing the App Version Number

## Version 1.2.2 - (2024-04-10) &#128247;

### **UI Update**

- Admin users in the the admin portal will now see an image of the product for each line item in the order.
- Mouseover the image to see a larger version of the image.
- This should help managers when making approval decisions as well as comparing the product the received against what was ordered.
- Many thanks to _Lauren Willis_ for the suggestion for this feature!
- Added a "Receive" button to the UI, hopefully &#129310; only for orders in the "Ordered" status. This will allows managers to mark the items as Received which should allow them to populate in the Inventory Management System.
  : TODO: The feature can be enhanced by passing the employee the item is being assigned into the function and assigning it to the employee in the Inventory System in addition to changing the status of the item.
- All requests with the status of "Pending" after 90 days from being submitted will automatically be "Expired". This should reduce the chance of old requests being approved in error, reduce the clutter managers see on their dashboard, and may improve load times as well.
- The main UI for Store Administration (Approve, Deny, Receive items, etc..) will not display Denied requests. This has been requested by several managers.
  : TODO: Add a method for managers to find denied request and change the status to approved (in the event it was denied in error, etc....)

### **Bug's Squished**

- Fixed a bug that allowed users to "Approve" orders that were already ordered; causing them to reenter the ordering pipeline. Approve buttons that were not intended to be displayed were displayed to the user next the the disabled approve button and the warning 'Orders in this status can not be edited.' ... which clearly was not accurate.
- Fixed an issue when viewing requests for employees who have separated from Employment that caused an index error in retrieving their fiscal year spending.

### **Database Maintenance**

- Several older orders were moved to 'Expired' status to be excluded from current queries. These were early orders that will never be received.
- Requests 90 days old with the status of 'Pending' were moved to 'Expired' status. This will be a weekly event (see above).

## Version 1.2.1 - (2024-03-7) &#128030;

### **Bug Squished**

- Fixed an error in the Inventory Management System (_yes we have one of those and it's pretty okay if you ask me_) lookup where employees with a seperation date greater than todays date were excluded from lookup. Thanks again _Kelly Herrin_ .
- Updated vendor report query to include only items with status of 'Ordered' (3/13/2024)

## Version 1.2.0 - (2024-03-4) &#128240;

### **Major UI Update**

- Product details page changed from 'product-details-onefee' to 'product-details'.

- This change is in preparation for product updates and changes coming soon.
- The products page will no longer display a color swatch for the selected item.
- &#128293; Each product image updates to show the product model wearing the selected color. This has been one of the most requested features. &#128293;
- If we missed one, shoot us an email at store.berkeleycountysc.gov and let us know! &#128591;
- The logo selection dropdown now also updates a small logo image near the "Add to Cart" button.
- Logo options of "black" or "white" have been consolidated as they are no longer needed. The color of the stitched logo is based on the color of the shirt. For a specific stitching color request please add in a comment.
- Product 185 (Gildan - Heavy Blend Hooded Sweatshirt) product view updated to reflect minimum order requirements and Department restrictions.

### **Bug's Squished**

- Fixed error in fiscal year date calculations for Employee Requests page for manager approvals.
- Changed query structure for new orders / customer information to exclude separated employees.

## Version 1.1.5 - (2024-02-01) &#129506;

### **Product Added**

- &#129506; &#129506; HATS! &#129506; &#129506;
- New hat options available
- Hat product view customized - see each hat live in full HD Color.

### **Admin Features Added**

- _Department Admin Page_
- Assign, Reassign, or reset Department Head, Department Assistant, and Department Asset Managers from UI.
- Page is still slow to load but I know why. Added to TODO list.
- Return from API is sorted by those departments with assignments already, those with none are near the bottom.
- _TODO:_ Sort / Filter function to make it easier to find the one you seek among the multitudes.
- When assigning an employee to a role within a department the users table will be checked and a user account will be created if one does not exist.

## Version 1.1.4 - (2024-01-29) &#129513;

### **Minor Feature Added**

- Link added to product details page above logo selector to open a new tab in .../logos.php
- Logos page shows users all the available logos in high res images. Clicking on a logo opens a nice popover style element to see the image in greater detail.
- Removed all Communications Logos from the query so they will not be displayed.

## Version 1.1.3 - (2024-01-24) &#128467;

### **Bug's Squished**

- Fixed an issue causing a fee to be applied twice.
- Searching from nav bar actually searches for text entered into the search bar, not just directs to you the search page to type it all in again. &#127879;
- Just fixed about 1,300 typos on this page.

### **Minor changes to database**

- Base cost, logo fee, and tax are captured individually. This will be valuable in the future when tax rates change allowing us to accurately report on amounts spent.

### **New Additions**

- About 10 new items were added to the store for your enjoyment; including a 7x, 8x, 9x, and 10x option for shirts.
- A new vendor was added to the store. While is not reflected in the users UI, it will allow for more diverse options down the road.

### **Feature added**

- At checkout users will see, after selecting who the order is for, a total of orders "Approved", "Ordered", and "Received" for the current fiscal year.
- In the admin section any line items that have the order status or "Ordered" the buttons for "Approve" and "Deny" are now disabled to prevent changes in status. Thanks to _Kelly Herrin_ for the suggestion on this one.

## Version 1.1.2 - (2023-12-14) &#127877;

### **Minor Bugs Squished**

- Fixed an issue where a specific product price would not update properly based on size when writing order to database.
- Updated vendor report template to tables and pages separated by vendor. This is helpful when one order has items from multiple vendors. The design should allow for one report that can be sent to both vendors easily, or each vendor can receive their specific page only. This leaves that decision up to the user.
- Added two new hats to the product line up!
- Updated several product images to render better in the product details view.
- Moved "Reports / Past Reports" from a side module on the admin page to it's own page. It can be found under "Reports" from the top or side nav depending on where you are.
- Added additional data fields to the "Reports" page to make finding the exact vendor report easier.
- Employee Requests page is now default landing page for Admin Login.
- Employee Requests page refactored to only display requests that are NOT status received. All received items should be managed through Inventory Management System. This should increase page load times and reduce the time it takes to find specific orders.
- Items requiring a Purchase Order (per the vendor) are flagged as such in the 'Employee Requests' section in admin backend.
- A 'Generate PO Request' button will be displayed if any of the items in that order require a Purchase Order. The PO Request will list only the items on the order that require a PO. This printable item is intended make it as simple as possible to provide the person(s) entering the PO request the information they need.

## Version 1.1.1 - (2023-10-05) &#128240;

### **Preparing for the Future**

- In preparation for some exciting changes coming to the store some database format changes were in order:
- Previous versions of pricing recorded in the database per order included the product price, logo fees, and stitching fees combined into one value. Each value will now be recorded individually in the database. End users should only see minor changes related to this.
- The "Order Success" screen after an order is placed now reflects the lower base unit price (without the embedded logo fees) as well as a line item for the logo fee under it.
- The email confirmation sent upon a successful request being placed also reflects the lower base price and a line item for the logo fees.
- The email confirmation section with the historical data for the employee was removed due to lack of interest. Actually if it was not noted in this changelog it is doubtful anyone would even have noticed.
- Admin reporting and vendor reporting have been updated to reflect this changes as well a base price and logo fee displayed in their own respective columns.
- **All previous orders** have had the base price of each line item reduced by $5.00 (_except for specific products that did not have a logo fee added to it_). Historical data and reporting should continue to be as accurate as it was before - however the two values will not be displayed as one.

## Version 1.1.0 - (2023-09-27) &#127381;

### **New Admin Features**

- Reporting for previous orders and invoice amounts has been updated to reflect both pre tax and post tax numbers for ease of use.
- When creating an order instance the person entering the order will now be prompted for a PO#. Leaving the input blank when there is no PO is the expected action.
- This feature is currently not supported in Firefox, but is in all other modern browsers. It is only available behind a flag in Firefox as of this update. [MDN Docs](https://developer.mozilla.org/en-US/docs/Web/API/Popover_API)
- The vendor reports now reflect the item pricing both with tax and without & the dev team doesn't care if vendors want to see both numbers or not. &#9749;
- The vendor report now shows the PO Number associated with the order instance on the top of the report. If there is not PO the value of 'N/A' is displayed.

## Version 1.0.9 - (2023-09-22) &#129513;

### **Minor UI changes**

- Created and updated logo for product #185.
- Update product #185 to post correct logo value in database when ordered.

## Version 1.0.8 - (2023-09-01) &#128030;

### **Minor Bugs Squished**

- Fixed an issue with "Continue Shopping" button on product view and cart view where adding item to cart or changing quantity in cart caused button to become a link to current page.
- Fixed an issue when in the event there are two entries in BIC with the same employee ID, but different names, the one without a separation date wins. This impacted a modal in the requests.php page and the managers ability to view/approve/deny the request.

### **UI tweaks**

- Added size chart under photo for shirts.

## Version 1.0.7 - (2023-08-21) &#128030;

### **Minor Bugs Squished**

- Fixed an issue where gender filter = N/A was not included in any results.

### **Product Added**

- New Product &#128293; added.

### **Admin Updates**

- _Making changes to admin backend to prep for adding additional vendors_
- Added vendor name and "requested for" name to viewOrdersByDept report. Currently items are sorted during query.
- TODO: Create tables for each employee with sub tables for different vendors.

## Version 1.0.6 - (2023-08-18) &#129513;

### **UI tweaks**

- Added department name placement value to display in approval screen under the logo
- Added department name placement value to display in confirmation email to employee

## Version 1.0.5 - (2023-08-08) &#128030;

### **Minor Bugs Squished**

- Fixed broken link in Nav
- Fixed bug that caused inactive products to display on front page

### Version 1.0.4 - (2023-07-31) &#129513;

### **UI tweaks**

- Set product specific CSS for logs images over layed on hats.
- Pushed new index page layout to production
- Added 4th top selling product to page solely for layout purposes.
- Support page links on right updated, nav removed to better direct traffic.

## Version 1.0.3 - (2023-07-27)

### New Features &#129327;

#### **New products details page**

- Added ability to select quantity of items before putting in the cart.
- Added Cart View Slide Out feature with interactive cart to view items in cart and delete items.
- Slideout feature is accessed using the cart icon in Nav that previously linked to the cart.
- Go To Cart button in slide out now takes users to cart.
- Logos were consolidated to reflect approved, department specific, logos.
- All other logos are shown in both black and white with "Department Name" under them to reflect what product will more closely resemble.
- Selecting the No Dept Name or Left Sleeve option will remove the Department Name from image.
- Logo Image now displays overlaid on the product.
- New Nav bar allows for quickly locating Mens and Women's products, with sub menus sorting by type to allow for locating products faster as well as faster page loads.

#### **TODO:**

- ~~Logo Images and Product Images are not uniform so not all render great. ie a few logos end up in an armpit. This was not intentional as a comment on product quality. Need to work on standard image sizes.~~
- New versions of main page with nav, and new version of search page need tested and pushed to production.
- Media Queries for mobile for all pages need refined based on new layout.

## Version 1.0.2 - (2023-07-24)

### New Features &#128079;

#### **New Email format and functionality for unavailable products**

- Updated emails sent to users when size or color is not available. (55ae7bf1)
- Email script was updated to include requested for and requested by addresses. (55ae7bf1)
- Email now provides users a list of options available based on the specific product. (55ae7bf1)
- List of options has the unavailable option removed from the list to avoid confusion. (55ae7bf1)

## Version 1.0.1 - (2023-07-24)

### Other Changes

- **other:** description[fixed typo in script] (69abd7ee)
- **test:** description [updated changelog func] (63daa23c)
- //github.com/Berkeley-County/county-store (fd94eeae)
- //github.com/Berkeley-County/county-store (395f8ac8)

## Version 1.0.0 - (2023-07-13)

### General Changes

- Added cart viewer slideout feature
- Fixed UI bug that caused shift in rendering in certain cases
- Changed product photo in "View Details" to the version without model
- Added the logo to the product image and allows the user to update the image from logo dropdown
