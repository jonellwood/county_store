# Using all these garbage scrips to automate products being added. The sequence of these matters.

### Run these in order

1. Load up urls.py array with the product urls from the sanmar website. ex. 'https://www.sanmar.com/p/8002_RiverBlNv?text=K100LS'

2. Run urls.py ### downloads color swatches and writes product info to text file for each url.

3. Run rename_main_prod_image.py

4. Run <code>prune_colors.py</code> ### removes extra text from the name of the color swatch .gif

5. Run <code>copy_color_files.py</code> ### copies any color swatch images that are not already there.

6. Run add_image_links.py ### downloads as image of the product of each color option.

   <!-- - This will open your browser and take focus... just let it run and then close out the billion tabs it opened. -->

7. Run prod_image_rename.py ### renames the images to {color}\*{product_number}.jpg

8. Run rename_color_in_prod_image.py ### renames color from vendor abbreviations to full color name using key value pair. If one is missing, add it.

9. Run add_sm.py ### renames the images to {color}\_SM-{product_number}.jpg

10. Move images from './temp_prod_img to '../product-images' to get ready to be FTP to production server.

11. Run get-color-ids-via-api.py ### gets a list of color names and returns their index value from the database.

12. Run extract_size_value.py ### gets a list of the size index values and appends to text file.

13. Run add-product-via-api.py ### adds product to the database

// THINGS TODO

<!-- - !!! Fix loop issue in add_image_links.py so it only opens each link once -->
<!-- - !!! Add script to get the first image link, download it, remname it 'SM-{product number}.jpg ** It is downloaded with the color swatches currently - just needs renamed \*\*** -->

- Add a check to see if the product number already exists in the database and handle and log accordingly.
- Add file rename to add-product-via-api.py to append .done (or something) so the file will not be processed twice.
