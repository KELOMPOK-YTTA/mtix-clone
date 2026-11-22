<?php

namespace Database\Seeders;

use App\Models\Movie;
use Illuminate\Database\Seeder;

class MovieSeeder extends Seeder
{
    public function run(): void
    {
        $moviesData = [
            [
                'slug' => 'petualangan-sang-naga',
                'title' => 'Petualangan Sang Naga',
                'synopsis' => 'Seorang pemuda desa menemukan telur naga misterius di hutan terlarang. Ketika telur itu menetas, ia harus memilih antara melindungi naga tersebut atau menyerahkannya kepada kerajaan yang ingin menggunakannya sebagai senjata perang.',
                'genre' => 'Fantasy',
                'duration_minutes' => 128,
                'format' => 'IMAX',
                'poster_url' => null,
                'release_date' => '2026-11-01',
                'status' => 'now_playing',
            ],
            [
                'slug' => 'gelap-tanpa-bintang',
                'title' => 'Gelap Tanpa Bintang',
                'synopsis' => 'Seorang detektif veteran menghadapi kasus pembunuhan berantai di kota metropolitan yang ternyata terhubung langsung dengan masa lalunya yang kelam. Waktu terus berjalan sementara korban berjatuhan satu per satu.',
                'genre' => 'Thriller',
                'duration_minutes' => 115,
                'format' => '2D',
                'poster_url' => null,
                'release_date' => '2026-10-20',
                'status' => 'now_playing',
            ],
            [
                'slug' => 'cinta-di-ujung-senja',
                'title' => 'Cinta di Ujung Senja',
                'synopsis' => 'Dua jiwa yang terpisah oleh jarak dan waktu akhirnya dipertemukan kembali di sebuah kafe kecil di tepi pantai Bali. Kisah cinta yang mengharukan tentang keberanian untuk mencintai kembali setelah luka yang panjang.',
                'genre' => 'Romance',
                'duration_minutes' => 105,
                'format' => '2D',
                'poster_url' => null,
                'release_date' => '2026-11-15',
                'status' => 'now_playing',
            ],
            [
                'slug' => 'galaksi-terakhir',
                'title' => 'Galaksi Terakhir',
                'synopsis' => 'Tahun 2187. Umat manusia terancam musnah ketika sebuah lubang hitam raksasa bergerak menuju tata surya. Sekelompok astronaut pemberani ditugaskan menjalankan misi mustahil untuk menghentikan kehancuran alam semesta.',
                'genre' => 'Sci-Fi',
                'duration_minutes' => 148,
                'format' => 'IMAX',
                'poster_url' => null,
                'release_date' => '2026-12-05',
                'status' => 'coming_soon',
            ],
            [
                'slug' => 'rumah-di-atas-awan',
                'title' => 'Rumah di Atas Awan',
                'synopsis' => 'Sebuah film animasi yang mengisahkan perjalanan seorang anak perempuan kecil bernama Bintang yang mencari ayahnya yang katanya tinggal di sebuah rumah ajaib di atas awan. Penuh dengan keajaiban dan pelajaran tentang keluarga.',
                'genre' => 'Animation',
                'duration_minutes' => 92,
                'format' => '3D',
                'poster_url' => null,
                'release_date' => '2026-12-20',
                'status' => 'coming_soon',
            ],
        ];

        foreach ($moviesData as $data) {
            Movie::firstOrCreate(
                ['slug' => $data['slug']],
                $data
            );
        }
    }
}
