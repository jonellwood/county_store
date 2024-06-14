import os

prod_img_path = './temp_prod_img'

def get_prod_num(filename):
    parts = filename.split('_')
    if len(parts) < 3:
        raise ValueError(f"Invalid filename format: {filename}")

    prod_number = '_' + parts[-1].split('front')[0]  # Extracting the product number
    prod_color = parts[-1].split('modelfront')[1]  # Extracting the product color
    return prod_number + prod_color + '.jpg'

def rename_img(prod_img_path):
    for filename in os.listdir(prod_img_path):
        if filename.endswith(".jpg"):
            try:
                new_filename = get_prod_num(filename)
                os.rename(os.path.join(prod_img_path, filename), os.path.join(prod_img_path, new_filename))
                print(f"Renaming {filename} to {new_filename}")

            except Exception as e:
                print(f"Error processing {filename}: {e}")
    print("Now run rename_color_in_prod_image.py")

rename_img(prod_img_path)