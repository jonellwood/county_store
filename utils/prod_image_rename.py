import os

prod_img_path = './temp_prod_img'

def get_prod_num(str, prod_color):
    if prod_color in str:
        print(str)
        print(prod_color)
        return str.split(prod_color)[0]
    else: 
        return str

def rename_img(prod_img_path):
    for filename in os.listdir(prod_img_path):
        if filename.endswith(".jpg"):
            try:
                parts = filename.split('-')
                if len(parts) < 3:
                    raise ValueError(f"Invalid filename format: {filename}")

                prod_color = parts[1].lower()
                prod_number = '_' + get_prod_num(parts[3].lower(), prod_color)
                file_ext = '.jpg'
                new_filename = prod_color + prod_number + file_ext
                # print(new_filename)
                os.rename(os.path.join(prod_img_path, filename), os.path.join(prod_img_path, new_filename))
                print(f"Renaming {filename} to {new_filename}")

            except Exception as e:
                print(f"Error processing {filename}: {e}")
    print("Now run rename_color_in_prod_image.py")

rename_img(prod_img_path)