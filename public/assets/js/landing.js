document.addEventListener("DOMContentLoaded",function(){


new Chart(document.getElementById("chartDoughnut"),{
type:"doughnut",
data:{
labels:["60 W","90 W","125 W","150 W","200 W","240 W","250 W"],
datasets:[{
data:[10,20,15,10,20,15,10],
backgroundColor:[
"#1a237e",
"#ffc107",
"#ff9800",
"#e91e63",
"#4caf50",
"#9c27b0",
"#03a9f4"
],
borderWidth:0
}]
},
options:{
cutout:"60%",
plugins:{legend:{display:false}}
}
});


new Chart(document.getElementById("chartBar"),{
type:"bar",
data:{
labels:["Total LPJU","Total Daya"],
datasets:[{
data:[21,3325],
backgroundColor:["#7c4dff","#ffca28"],
borderRadius:6
}]
},
options:{
plugins:{legend:{display:false}},
scales:{y:{beginAtZero:true}}
}
});


new Chart(document.getElementById("chartPie"),{
type:"pie",
data:{
labels:[
"PJU Jalan Umum",
"PJU Jalan Provinsi",
"PJU Jalan Negara",
"PJU Dana Istimewa",
"PJU Permukiman"
],
datasets:[{
data:[1,1,3,10,3],
backgroundColor:[
"#e91e63",
"#4caf50",
"#ff9800",
"#03a9f4",
"#8bc34a"
],
borderWidth:0
}]
},
options:{
plugins:{legend:{display:false}}
}
});


});