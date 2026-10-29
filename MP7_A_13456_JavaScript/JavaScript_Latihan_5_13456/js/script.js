function fn_ValForm() {
    var sMsg = "";
    var sInfo = "";
    var sPilihan = "";

    var sNama = document.getElementById("nama").value;
    var sEmail = document.getElementById("email").value
    var sUmur = document.getElementById("umur").value
    var sKat = document.getElementById("kategori").value
    var oRegex = /^[a-z0-9][a-z0-9_\.-]{0,}[a-z0-9]@[a-z0-9][a-z0-9_\.-]{0,}[a-z0-9][\.][a-z0-9]{2,4}$/;
if(sNama == "") {
    sMsg += "\n* Anda belum mengisi nama"; 
}
if(sEmail == ""){
    sMsg += "\n* Anda belum mengisi email";
}else if(!oRegex.tes(sEmail)){
sMsg += "\n*email tidak valid";
}


if(sUmur == ""){
    sMsg += "\n* Anda belum mengisi umur"
}else if(isNaN(sUmur)){
    sMsg += "\n* umur harus berupa angka";
}else{
    var iUmur == parseInt
}

iUmur = prompt("Masukkan Umur.", 0);
sKeterangan = prompt("Kategori (J)unior atau (S)enior", "J");

if(iNilai <= 14) {
    if(sKeterangan == "J") {
        alert("kategori Junior");
    }
}else{
    if (sKeterangan == "J") {
        alert("Kategori Senior");
    }
}


    sKategori = prompt("Pilih Lomba: (C)erdas cermat, (P)uisi, (D)esain Poster", "C");

        switch (sInput) {
            case "C":
                alert("Cerdas Cermat");
                break;
            case "P":
                alert("Puisi");
                break;
            case "D":
                alert("Design Poster");
                break;
            default:
                alert("Menu tidak tersedia");