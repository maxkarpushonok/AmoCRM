function getXmlHttp(){
    var xmlhttp;
    try {
        xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
    } catch (e) {
        try {
            xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
        } catch (E) {
            xmlhttp = false;
        }
    }
    if (!xmlhttp && typeof XMLHttpRequest!='undefined') {
        xmlhttp = new XMLHttpRequest();
    }
    return xmlhttp;
}

function escapeHtml(text) {
    var map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };

    return text.replace(/[&<>"']/g, function(m) { return map[m]; });
}

function checkInput(string) {
    return string.match(/^[A-z0-9]+$/);
}

function checkEmail(string) {
    return string.match(/^([0-9A-z]{1,}[-._]{0,1}){1,4}@([0-9A-z]{1,}[-]{0,1}[0-9A-z]{1,}\.){1,2}[A-z]{2,}$/);
}

result.auth.accounts = undefined;

function auth(element) {
    let login = escapeHtml(document.getElementById('login').value);
    let hash = escapeHtml(document.getElementById('hash').value);
    let subDomain = escapeHtml(document.getElementById('subDomain').value);

    if (login == '') {
        document.getElementById('authResult').innerHTML = '<span style="color: #990000">Login is empty!</span>';
        return;
    }

    if (checkEmail(login) == null) {
        document.getElementById('authResult').innerHTML = '<span style="color: #990000">Login is incorrect!</span>';
        return;
    }

    if (hash == '') {
        document.getElementById('authResult').innerHTML = '<span style="color: #990000">Hash is empty!</span>';
        return;
    }

    if (checkInput(hash) == null) {
        document.getElementById('authResult').innerHTML = '<span style="color: #990000">Hash is incorrect!</span>';
        return;
    }


    if (subDomain == '') {
        document.getElementById('authResult').innerHTML = '<span style="color: #990000">Sub domain is empty!</span>';
        return;
    }

    if (checkInput(subDomain) == null) {
        document.getElementById('authResult').innerHTML = '<span style="color: #990000">Sub domain is incorrect!</span>';
        return;
    }

    let req = getXmlHttp();
    const url = 'ajax.php';
    const params = element.id + '=true&login=' + login + '&hash=' + hash + '&subDomain=' + subDomain;

    req.responseType = 'json';
    req.open('POST', url, true);
    req.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');

    req.addEventListener('readystatechange', () => {
        if(req.readyState === 4 && req.status === 200) {
            var ajaxAuthResult = req.response;
            if (ajaxAuthResult == null) {
                var authResult = document.getElementById('authResult');
                authResult.innerHTML = '<span style="color: #990000">Incorrect login or password!</span>';
            } else {
                if (ajaxAuthResult.result) {
                    document.getElementById('authorization').style.display = 'none';
                    document.getElementById('dataList').style.display = 'block';
                    document.getElementById('helloUser').innerHTML = 'Hello ' + ajaxAuthResult.auth.name + '!';
                    document.getElementById('leadsData').innerHTML = ajaxAuthResult.leads;
                    document.getElementById('tasksData').innerHTML = ajaxAuthResult.tasks;
                    document.getElementById('leadsDataWithoutTasks').innerHTML = ajaxAuthResult.leadsIdWithoutTasks;

                    if (element.id == 'add') {
                        document.getElementById('add').style.display = 'none';
                        document.getElementById('addResult').style.display = "block";
                        document.getElementById('addResult').innerHTML = ajaxAuthResult.addMessage;
                    }

                    if (ajaxAuthResult.leadsWithoutTasks == false)
                        document.getElementById('add').style.display = 'none';
                } else {
                    document.getElementById('authResult').innerHTML = '<span style="color: #990000">' + ajaxAuthResult.message + '</span>';
                }
            }
        }
    });

    req.send(params);
}