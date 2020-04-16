import os
# pip install Pillow
from PIL import Image


def scale_image(input_image_path,
                output_image_path,
                width=None,
                height=None
                ):
    original_image = Image.open(input_image_path)
    w, h = original_image.size
    print('The original image size is {wide} wide x {height} '
          'high'.format(wide=w, height=h))

    if width and height:
        max_size = (width, height)
    elif width:
        max_size = (width, h)
    elif height:
        max_size = (w, height)
    else:
        # No width or height specified
        raise RuntimeError('Width or height required!')

    original_image.thumbnail(max_size, Image.ANTIALIAS)
    original_image.save(output_image_path)

    scaled_image = Image.open(output_image_path)
    width, height = scaled_image.size
    print('The scaled image size is {wide} wide x {height} '
          'high'.format(wide=width, height=height))


os.chdir('../public/uploads/users/')
path = os.getcwd()
extensions = ['png', 'jpg', 'jpeg', 'gif']

for root, dirs, files in os.walk(path):
    for _file in files:
        _file_temp = _file.split('.')
        if len(_file_temp) >= 2:
            fileExtension = _file_temp[-1].lower()
            if fileExtension in extensions:
                _ava_path = os.path.join(root, _file)
                # print(_ava_path)
                scale_image(input_image_path=_ava_path,
                            output_image_path=_ava_path,
                            width=512)
