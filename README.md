# 🎶 stats.fm-card

Create **svg cards** to showcase your top Spotify artists, tracks, or albums on any website or README.

Images are cached for 1 day to reduce load time by a significant amount.

![PHP](https://img.shields.io/badge/php-%23777BB4.svg?style=for-the-badge&logo=php&logoColor=white)

## 🏡 Host 

Host the code on any server that **support php** and then install the package using **composer** : 
```sh
composer install
```

I recommand creating a cron job that run the **clean.php** script. This script remove all cached images if they are older than 7 days. This will avoid using to much space for nothing.

## 🚀 Use

To generate the image you need only provide the link where your script is hosted along with your username. For example, `https://example.com?username=sheldon_cooper`.

Customize your card further with additional parameters:

| Param     | Default  | Description                                      |
|-----------|----------|--------------------------------------------------|
| **range**     | lifetime | Range for the stats (weeks, months, lifetime)    |
| **type**      | artists  | Type of stat displayed (artists, tracks, albums) |
| **limit**     | 5        | Limits the number of items displayed             |
| **width**     | 600      | Width of the container                           |
| **height**    | 180      | Height of the container                          |
| **spacing**   | 20       | The space between the items                      |
| **y_offset**  | 10       | Y-axis offset of odd elements                    |
| **rounded**   | 4        | Container border radius                          |
| **i_rounded** | 100      | Elements border radius                           |
| **g_start**   | 0D1117   | Gradient start color                             |
| **g_stop**    | 000000   | Gradient end color                               |


## 🖼️Examples


## 🤝Contributing

Im open to contributions! If you'd like to contribute, please create a pull request and I'll review it as soon as I can.

## 📝 License

This project is licensed under the MIT License - see the LICENSE file for details.