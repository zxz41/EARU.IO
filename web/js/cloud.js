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
    let form = document.createElement("form");
    form.method = "POST";

    let dirinput = document.createElement("input");
    dirinput.type = "hidden";
    dirinput.name = "DirectoryName";
    dirinput.value = dir;

    let actioninput = document.createElement("input");
    actioninput.type = "hidden";
    actioninput.name = "ChangeDirectory";
    actioninput.value = "true";

    form.appendChild(dirinput);
    form.appendChild(actioninput);
    document.body.appendChild(form);
    form.submit();
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
    let namecat = categories[0];
    let typecat = categories[3];

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