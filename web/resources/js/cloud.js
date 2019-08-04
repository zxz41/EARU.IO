// Loader when submitting forms
let loader = document.getElementById("loader");
let forms  = document.getElementsByTagName("form");
for(let form of forms)
{
    if(!form.classList.contains("no-loader"))
    {
        form.addEventListener("submit",() => {
            loader.style.display = "block";
        });
    }
}

// Selecting files, folder, navigation
let rows      = document.getElementsByClassName("file-row");
let sfinputs  = document.getElementsByClassName("selected-file-input");
let sfndivs   = document.getElementsByClassName("selected-file-name");
let sbgcol    = "rgba(244, 47, 44, 0.65)";
let scol      = "#333333";
let selement  = null;
let sfilename = "";
let oldstyle  = { BackgroundColor: "", Color: "" };

function ChangeDirectory(dir)
{
    let form    = document.createElement("form");
    form.method = "POST";

    let dirinput   = document.createElement("input");
    dirinput.type  = "hidden";
    dirinput.name  = "DirectoryName";
    dirinput.value = dir;

    let actioninput   = document.createElement("input");
    actioninput.type  = "hidden";
    actioninput.name  = "ChangeDirectory";
    actioninput.value = "true";

    form.appendChild(dirinput);
    form.appendChild(actioninput);
    document.body.appendChild(form);
    form.submit();

    loader.style.display = "block";
}

function OnRowClick(element)
{
    if(selement != null)
    {
        selement.style.backgroundColor = oldstyle.BackgroundColor;
        selement.style.color = oldstyle.Color;
    }
    else
    {
        let elements = document.getElementsByClassName("has-selected-file");
        for(let e of elements)
            e.style.display = "block";

        elements = document.getElementsByClassName("no-selected-file");
        for(let e of elements)
            e.style.display = "none";
    }

    let categories = element.getElementsByClassName("file-row-category");
    let namecat    = categories[0];
    let typecat    = categories[3];

    if(typecat.innerText === "folder" && selement === element)
        ChangeDirectory(namecat.innerText);

    selement = element;

    let style = element.style;
    oldstyle = {
        BackgroundColor: style.backgroundColor,
        Color: style.color
    };
    style.backgroundColor = sbgcol;
    style.color = scol;

    sfilename = namecat.innerText;

    for(let input of sfinputs)
        input.value = sfilename;

    for(let div of sfndivs)
        div.textContent = sfilename;
}

for(let row of rows)
    row.addEventListener("click", () => OnRowClick(row));

// Shows file name when uploading and prevents big file uploads
let fileform  = document.getElementById("upload_form");
let filewarn  = document.getElementById("file-warning");
let fileinput = document.getElementById("file-upload");
let filename  = document.getElementById("file-upload-name");
let fpathlen  = 12
let fmaxsize  = 52428800 //50mb

fileinput.addEventListener("change",() =>
{
    let len = fileinput.value.length;
    filewarn.style.display = "none";
    filename.value = "~/" + fileinput.value.substr(12,len - 12);
})

fileform.addEventListener("submit",evt =>
{
    let file = fileinput.files[0];
    if(file && file.size < fmaxsize)
    {
        fileform.submit();
    }
    else
    {
        evt.preventDefault();
        filewarn.style.display = "inline";
        loader.style.display = "none";
    }
},false);