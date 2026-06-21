# İsabet Academy — Admissions Program Finder
## İnceleme & İyileştirme Raporu

**Tarih:** 2026-06-21
**Kapsam:** `mf-admissions` eklentisi (v1.0.0) + brief uyumu + site sunumu
**Genel değerlendirme:** Sağlam, doğru kurgulanmış bir eklenti. Brief'in 1. maddesindeki “cinsiyet + yaş bazlı yönlendirme” ihtiyacını tam karşılıyor. Asıl kazanım, **içeriğin zenginleştirilmesi**, **bir konfigürasyon doğrulaması** ve **eski/ölü kodun temizlenmesi** ile gelecek.

---

## 1. Eklenti ne yapıyor? (Özet)

Ziyaretçi iki soruya cevap veriyor — *“Öğrenci kim?”* (erkek/kız) ve *“Sınıf / yaş?”* — eklenti anında doğru program kartını ve ilgili sayfaya giden bir CTA gösteriyor. Sayfa yenilenmiyor, seçim URL’de taşınıyor (`?gender=male&grade=5`), analytics olayları tetikleniyor.

Bu, brief'teki şu cümlenin birebir teknik karşılığı:
> *“Ziyaretçi 'kız mı / erkek mi' ve 'kaç yaşında' sorularına göre yönlendirilebilmeli. Cinsiyet ve yaş bazlı net yönlendirme olmadan kafa karışıklığı yaşanır.”*

---

## 2. Brief ↔ Eklenti uyumu

| Brief kuralı | Eklenti karar tablosu (seed) | Durum |
|---|---|---|
| Erkek 3–4: Day Student | `male 3–4` | ✅ |
| Erkek 5–12: Full Boarding | `male 5–12` | ✅ |
| Kız 6–12: Full Boarding | `female 6–11` + `female 12` | ⚠️ Garip bölünme (aşağıda) |
| Kız 3–5: yatılı **yok** | `female 3–5` (sayfa atanmamış) | ⚠️ “Bulunamadı” hatası gibi görünüyor |
| Anaokulu 3-4-5 (her iki cinsiyet) | `male 0` + `female 0` | ✅ |
| Dil Okulu (bağımsız) | Karar tablosuna ayrı satır eklenebilir | ➖ Opsiyonel |
| Kız High School → Delaware notu | Ayrı not gösterilemiyor | ❌ Eksik (aşağıda) |
| Anaokulu “tuvalet eğitimi şart” notu | Kart üzerinde not alanı yok | ❌ Eksik (aşağıda) |

---

## 3. Güçlü yönler (korunmalı)

- **Karar tablosu kod içinde sabit değil** — admin panelinden düzenleniyor. Yeni sınıf/sayfa eklemek kod gerektirmiyor.
- **Erişilebilirlik temeli iyi:** `aria-pressed`, `aria-live="polite"`, `role="group"`, klavye ile gezilebilir, 44–48px dokunma hedefleri, `prefers-reduced-motion` desteği.
- **Güvenlik doğru:** nonce kontrolleri, `current_user_can`, tüm çıktılarda `esc_*`, girdi sanitizasyonu, `ABSPATH` koruması.
- **Çıkmaz yol yok:** eşleşme/sayfa yoksa iletişim CTA’sına düşüyor.
- **WPBakery entegrasyonu** + shortcode, ikisi birden mevcut.
- **Analytics** (dataLayer / gtag / CustomEvent) hazır — dönüşüm ölçülebilir.
- CSS tema ile çakışmayacak şekilde `.admissions-finder` altında kapsüllenmiş.

---

## 4. Bulgular ve iyileştirmeler (öncelik sırasıyla)

### 🔴 P0 — Önce bunları doğrula/düzelt

**P0.1 — Karar tablosunda sayfalar atanmış mı?**
Seed (varsayılan) tüm satırları `page = 0` ile oluşturuyor (`class-admissions-rules.php:170-180`). Yani aktivasyondan sonra **admin her satıra hedef sayfayı elle seçmezse**, finder her seçimde “eşleşme bulunamadı → iletişim” fallback’ine düşer. Sayfalar yeni yayınlandığına göre **ilk kontrol bu olmalı:** Admissions → Decision Table’da her satırın doğru sayfaya (Isabet Academy Boy, Bedia Sultan, Preschool, Language School…) bağlı olduğunu doğrula.

**P0.2 — Kız 3–5 “dead-end” negatif mesaj veriyor.**
Brief’e göre kız 3–5 için yatılı program *yok* — bu geçerli bir durum, hata değil. Ama şu an bu seçim, gerçek hatalarla aynı mesajı gösteriyor:
> *“We couldn’t find a match for this selection…”*
Bu, veliyi “yanlış bir şey yaptım” hissine sokar. **Öneri:** bu senaryoya özel, açıklayıcı bir mesaj (“Şu an kız öğrenciler için 3–5. sınıf aralığında yatılı program sunulmuyor; Anaokulu / Dil Okulu seçeneklerini değerlendirebilir veya bize ulaşabilirsiniz”) + ilgili sayfalara link. Teknik olarak: bu satıra bir “bilgi sayfası” atayın **veya** finder’a “geçerli ama program yok” durumu için ayrı `i18n` metni ekleyin.

---

### 🟠 P1 — Sunumu güçlendiren içerik & UX

**P1.1 — Sonuç kartı çok zayıf (en yüksek etkili iyileştirme).**
Şu an kart sadece **sayfa başlığı + otomatik kırpılmış özet** gösteriyor (`class-admissions-finder.php:75-84`). Otomatik özet WPBakery sayfalarında dağınık/alakasız metin üretebilir. İyileştirmeler:
- Her kurala **elle yazılmış kısa açıklama** alanı (otomatik özet yerine).
- **Program görseli** (sayfanın öne çıkan görseli) — görsel kart dönüşümü ciddi artırır.
- **Tip rozeti** (“Full Boarding” / “Day Student” / “Preschool”) — veli bir bakışta türü anlar.
- **Koşul/not satırı** (örn. anaokulu için *“Tuvalet eğitimi almış olması gerekir”*; kızlar için *“High School, Delaware şubesinde”*).

> Not: Bu alanların **tamamı eski sürümde zaten kodlanmıştı** — bkz. P2.1. Yani “sıfırdan yapım” değil, mevcut altyapıyı geri bağlama işi.

**P1.2 — Delaware (High School) ayrımı kayıp.**
Brief: kızların High School’u Delaware şubesinde. Şu anki seed bölünmesi `female 6–11` + `female 12` anlamlı bir sınıra denk gelmiyor. Mantıklı bölünme: **6–8 (Middle, ana kampüs)** ve **9–12 (High School, Delaware notu)**. Karar tablosunu bu iki aralığa göre düzenleyip 9–12 kartına Delaware notunu ekleyin.

**P1.3 — Adım 2’ye odak (focus) geçmiyor.**
Cinsiyet seçilince 2. adım görünür oluyor ama klavye/ekran okuyucu kullanıcısının odağı taşınmıyor (`admissions-finder.js:194-204`). Adım 2 açıldığında ilk sınıf butonuna `focus()` verilmesi erişilebilirliği tamamlar.

**P1.4 — “Baştan başla / seçimi değiştir” yok.**
Sonuç gösterildikten sonra seçimi değiştirmek için yukarı kaydırmak gerekiyor. Karta küçük bir “Seçimi değiştir” bağlantısı akışı rahatlatır.

**P1.5 — Tuition’a yumuşak köprü.**
Brief 3. madde: ücretler “soft” sunulmalı (*“Contact us for tuition details”*). Sonuç kartındaki ikincil CTA, genel iletişim yerine doğrudan **Admissions sayfasının “Tuition & Financial Aid” accordion’una** (anchor link) gidebilir.

---

### 🟡 P2 — Kod sağlığı & teknik borç

**P2.1 — `class-admissions-programs.php` tamamen ölü kod (265 satır).**
`Admissions::init()` yalnızca `Admissions_Finder` ve `Admissions_Admin`’i başlatıyor (`class-admissions.php:41-53`); `Admissions_Programs` hiç `hooks()`’lanmıyor. Yani:
- “Programs” custom post type’ı **hiç kaydedilmiyor**,
- `types()` içindeki `info`/`none` türleri, `get_data()`, meta box — **hepsi kullanılmıyor**,
- CSS’teki `__badge`, `__meta`, `__note` stilleri JS tarafından **hiç render edilmiyor** (`admissions-finder.js` bunları üretmiyor).

İki seçenek:
- **(A) Temizle:** dosyayı ve ölü stilleri kaldır → eklenti yalınlaşır.
- **(B) Canlandır (önerilen):** P1.1 için tam da bu altyapı gerekiyor (kısa açıklama, tip, not, görsel). Programs CPT’yi geri bağlayıp kuralları sayfa yerine “program”a eşleştirmek, zengin kartların en temiz yolu.

> Karar P1.1’e bağlı: zengin kart isteniyorsa (B), istenmiyorsa (A). Şu an iki mimari yarım yarım duruyor.

**P2.2 — Ön-seçim analytics’i kirletiyor.**
URL’de `?gender=&grade=` varken finder, butonlara programatik `.click()` atıyor (`admissions-finder.js:223-234`); bu da `program_finder_step1/step2` olaylarını **kullanıcı tıklamamış olmasına rağmen** tetikliyor. Ön-seçim, olay göndermeden state’i kurmalı (ayrı bir “preselect” fonksiyonu).

**P2.3 — `menu_position 26` çakışması (potansiyel).**
Hem (ölü) Programs CPT hem Admin menüsü `26` kullanıyor. Programs canlanırsa menü sırası çakışır; canlandırma yapılırsa pozisyonları ayırın.

**P2.4 — Boş `languages/` klasörü.**
Metin domain’i `admissions` ve `load_plugin_textdomain` çağrılıyor ama `.pot/.po` yok. Site İngilizce olduğu için sorun değil; ileride TR sürüm gerekirse `.pot` üretin.

**P2.5 — `readme.txt` ile gerçek davranış uyuşmuyor.**
Readme “Programs are managed as a separate content type” diyor (satır 23-24) — ama P2.1 gereği bu özellik aktif değil. Hangi mimari seçilirse readme ona göre güncellenmeli.

---

## 5. Site mimarisi / sunum önerileri

Brief, Academics menüsünün ziyaretçiyi cinsiyet+yaşa göre yönlendirmesini istiyor. Finder bunun için ideal — sadece Admissions’ta değil:

1. **Academics landing sayfasına da koyun.** Brief’in “yönlendirme” mantığı tam olarak burada yaşar. Finder, Academics’in giriş bileşeni olmalı.
2. **Anasayfaya kompakt bir giriş.** “Doğru programı bulun →” mini bloğu finder’a/Academics’e götürsün.
3. **Program sayfalarını karşılıklı bağlayın.** Sonuç kartı → program sayfası; program sayfasında da “Başvur” → Admissions. Döngüyü kapatın.
4. **Boarding/Life on Campus:** brief ayrı, detaylı bir “Boarding Experience / Life on Campus” sayfası istiyor. Sitede **“Student Life”** var — bunun boarding deneyimini (yurt, günlük akış, hafta sonu) kapsadığından emin olun; yatılı kartlarından (erkek 5–12, kız 6–12) buraya ikincil link verilebilir.
5. **Mevcut yardımcı sayfaları finder’a bağlayın:** “Isabet Comparison”, “Academic Calendar”, “Weekly Program” — ilgili sonuç kartlarında ek linkler olarak değer üretir.

---

## 6. Hızlı aksiyon listesi

| # | Aksiyon | Öncelik | Efor |
|---|---|---|---|
| 1 | Decision Table’da her satıra doğru sayfa atandığını doğrula | 🔴 P0 | 10 dk |
| 2 | Kız 3–5 için pozitif/açıklayıcı mesaj | 🔴 P0 | Küçük |
| 3 | Sonuç kartını zenginleştir (görsel + elle açıklama + tip + not) | 🟠 P1 | Orta |
| 4 | Delaware ayrımı: kız 6–8 / 9–12 bölünmesi + not | 🟠 P1 | Küçük |
| 5 | Adım 2’ye focus, “baştan başla” linki | 🟠 P1 | Küçük |
| 6 | Tuition accordion’una yumuşak köprü | 🟠 P1 | Küçük |
| 7 | Ölü kodu temizle **veya** Programs CPT’yi canlandır (3’e bağlı) | 🟡 P2 | Orta |
| 8 | Ön-seçimde analytics tetiklenmesini durdur | 🟡 P2 | Küçük |
| 9 | Finder’ı Academics + Anasayfa’ya da yerleştir | 🟠 P1 | 15 dk |
| 10 | readme.txt’yi gerçek davranışla eşitle | 🟡 P2 | Küçük |

---

**Tek cümlelik sonuç:** İskelet doğru ve güvenli; en yüksek getiri, **sonuç kartını zenginleştirmek** (zaten yarısı kodlanmış altyapıyı geri bağlayarak), **kız 3–5 ile Delaware senaryolarını netleştirmek** ve **finder’ı Academics’e taşıyarak** brief’in yönlendirme vaadini tam yerine getirmekten gelecek.
