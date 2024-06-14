import os

start_img_path = './images_down'
final_img_path = './temp_prod_img'

#####COME BACK TO THIS LATER

def remove_main_prod_image(start_img_path):
    for filename in os.listdir(start_img_path):
        if filename.endswith(".jpg"):
            try:
                os.remove(os.path.join(start_img_path, filename))
                print(f"Deleted {filename} from {start_img_path}")
            except (FileNotFoundError, PermissionError) as e:
                print(f"Error deleting {filename}: {e}")
            except Exception as e:
                print(f"Unexpected error processing {filename}: {e}")
remove_main_prod_image(start_img_path)