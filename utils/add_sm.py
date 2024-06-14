import os 

prod_img_path = './temp_prod_img'
# prod_img_path = './images_down'
production_prod_images = '../product-images'
def add_sm(prod_img_path):
    for filename in os.listdir(prod_img_path):
        if filename.endswith(".jpg"):
            try:
                
                parts = filename.split('_')
                print(parts)
                prod_color = parts[0]
                basename = parts[1]
                print(basename)
                new_filename = prod_color + '_sm-' + basename 
                print(new_filename)
                os.rename(os.path.join(prod_img_path, filename), os.path.join(production_prod_images, new_filename))
                print(f"Renaming {filename} to {new_filename} and moving to {production_prod_images}")

            except Exception as e:
                print(f"Error processing {filename}: {e}")
    print("Now run get-color-ids-via-api.py")
add_sm(prod_img_path)