<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('courts', function (Blueprint $table) {
            $table->id();
            $table->string('court_name_ar')->nullable();
            $table->string('court_name_en')->nullable();

            // Court-specific dropdown fields (will be populated later)
            $table->foreignId('court_circuit')->nullable()->constrained('option_values')->nullOnDelete();
            $table->foreignId('court_circuit_secretary')->nullable()->constrained('option_values')->nullOnDelete();
            $table->foreignId('court_floor')->nullable()->constrained('option_values')->nullOnDelete();
            $table->foreignId('court_hall')->nullable()->constrained('option_values')->nullOnDelete();

            $table->boolean('is_active')->default(true);

            // Audit columns
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('court_name_ar');
            $table->index('court_name_en');
            $table->index('is_active');

            // Foreign keys
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
        });

        // Disable auto-increment for import compatibility
        DB::statement('ALTER TABLE courts MODIFY COLUMN id INT(11) NOT NULL');

        // Seed 52 courts from CSV
        $courts = [
            ['6 أكتوبر', '6th of October'],
            ['استئناف 6 أكتوبر', '6th of October Court of Appeals'],
            ['استئناف الجيزة', 'Giza Court of Appeals'],
            ['استئناف الزقازيق', 'Zagazig Court of Appeals'],
            ['استئناف القاهرة', 'Cairo Court of Appeals'],
            ['استئناف شبين الكوم', 'Shebeen El Kom Court of Appeals'],
            ['استئناف شمال الجيزة', 'North Giza Court of Appeals'],
            ['استئناف شمال القاهرة', 'North Cairo Court of Appeals'],
            ['استئناف عالي المنصورة', 'Mansoura High Court of Appeal'],
            ['الإدارية العليا', 'Supreme Administrative Court'],
            ['الإسكندرية', 'Alexandria'],
            ['الإسكندرية الاقتصادية', 'Alexandria Economic'],
            ['البساتين الجزئية', 'El Basatin Partial'],
            ['الجيزة الابتدائية', 'Giza Primary'],
            ['الدقي', 'Dokki'],
            ['الدقي الجزئية', 'Dokki Partial'],
            ['العبور الجزئية', 'El Obour Partial'],
            ['العجوزة', 'Agouza'],
            ['القاهرة الاقتصادية', 'Cairo Economic Court'],
            ['القاهرة الجديدة', 'New Cairo'],
            ['القاهرة للأمور المستعجلة', 'Cairo Court of Urgent Matters'],
            ['القضاء الإداري بالإسكندرية', 'Administrative Judiciary in Alexandria'],
            ['القضاء الإداري بالعباسية', 'Administrative Judiciary in Abbasiya'],
            ['المنصورة', 'Mansoura'],
            ['النزهة الجزئية', 'El Nozha Partial'],
            ['النقض', 'Court of Cassation'],
            ['الهيئة العامة للاستثمار والمناطق الحرة', 'General Authority for Investment and Free Zones'],
            ['إمبابة الجزئية', 'Imbaba Partial'],
            ['جنح منفلوط', 'Misdemeanors of Manfalout'],
            ['جنوب الجيزة', 'South Giza'],
            ['جنوب القاهرة', 'South Cairo'],
            ['حلوان', 'Helwan'],
            ['رشيد الكلية', 'Rashid General Court'],
            ['شبين الكوم', 'Shebeen El Kom'],
            ['شمال الجيزة', 'North Giza'],
            ['شمال القاهرة', 'North Cairo'],
            ['شمال القاهرة الإبتدائية', 'North Cairo Primary'],
            ['شمال أسيوط', 'North Assiut'],
            ['قسم شرطة مصر الجديدة', 'Heliopolis Police Station'],
            ['قصر النيل', 'Kasr El-Nil'],
            ['مصلحة الضرائب', 'Tax Authority'],
            ['نقابة الأطباء', 'Medical Syndicate'],
            ['نيابة 6 أكتوبر', '6th of October Public Prosecution'],
            ['نيابة الأموال العامة العليا', 'Supreme Public Funds Prosecution'],
            ['نيابة الزقازيق', 'Zagazig Public Prosecution'],
            ['نيابة الشئون المالية والتجارية', 'Public Prosecution for Financial and Commercial Affairs'],
            ['نيابة العامرية ثاني', 'El Amriya II Public Prosecution'],
            ['نيابة الهرم', 'El Haram Public Prosecution'],
            ['نيابة شبين الكوم', 'Shebin El-Kom Public Prosecution'],
            ['نيابة مدينة نصر أول الجزئية', 'Nasr City First Partial Public Prosecution'],
            ['هيئة الاستثمار - لجان فض المنازعات', 'General Authority for Investment - Dispute Resolution Committees'],
            ['هيئة الرقابة المالية، والبورصة المصرية', 'Financial Regulatory Authority, and the Egyptian Stock Exchange'],
        ];

        $id = 1;
        foreach ($courts as $court) {
            DB::table('courts')->insert([
                'id' => $id++,
                'court_name_ar' => $court[0],
                'court_name_en' => $court[1],
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Re-enable auto-increment
        DB::statement('ALTER TABLE courts MODIFY COLUMN id INT(11) NOT NULL AUTO_INCREMENT');

        Schema::dropIfExists('courts');
    }
};
