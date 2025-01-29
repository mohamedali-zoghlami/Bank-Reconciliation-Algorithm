
function excel(livre,releve,response){
    return new Promise((resolve, reject) => {
        const reader = new FileReader();
        const reader2 = new FileReader();
        let base64Data = response.releve;
        base64Data = base64Data.trim();
        let binaryString = atob(base64Data);
        const workbook1 = XLSX.read(binaryString, {type: 'binary'});
        let firstSheetName = workbook1.SheetNames[0];
        let firstSheet = workbook1.Sheets[firstSheetName];
        let releveTwoRows = XLSX.utils.sheet_to_json(firstSheet, { header: 1, range: "A1:Z2" });
         base64Data = response.journal;
        base64Data = base64Data.trim();
        binaryString = atob(base64Data);

        const workbook2 = XLSX.read(binaryString, {type: 'binary'});
         firstSheetName = workbook2.SheetNames[0];
         firstSheet = workbook2.Sheets[firstSheetName];
        var journalTwoRows = XLSX.utils.sheet_to_json(firstSheet, { header: 1, range: "A1:Z2" });

        reader.onload = function(event) {
            const data = new Uint8Array(event.target.result);
            // Pass data to SheetJS for parsing
            const workbook = XLSX.read(data, { type: 'array' });
            const sheetName = workbook.SheetNames[0];
            const worksheet = workbook.Sheets[sheetName];
            const jsonData = XLSX.utils.sheet_to_json(worksheet, { header: 1 });
            const firstRowsJournal=XLSX.utils.sheet_to_json(worksheet, { header: 1, range: "A1:Z2" });
            if(firstRowsJournal.length<2)
                {
                    resolve("Veuillez charger le même template du Grand livre bancaire !");
                    return;
                }
            for(i=0;i<firstRowsJournal.length;i++)
                {
                    if(notEqualTo(firstRowsJournal[i],journalTwoRows[i]))
                        {
                            resolve("Veuillez charger le même template du Grand livre bancaire !");
                            return;
                        }
                }
            let result=[];
            for(i=2;i<jsonData.length;i++){
                    let libele = jsonData[i][3];
                    let num = jsonData[i][2];
                    let date = jsonData[i][1];
                    let numacc=jsonData[i][0];
                    let debit=jsonData[i][4];
                    let credit=jsonData[i][5];
                    let solde = jsonData[i][6];
                    // Check if date or montant is missing
                    if (date && (debit || credit)) {
                        if(debit==undefined)
                       { debit=0;}
                        if(credit==undefined)
                            {
                                credit=0;
                            }
                            if(solde==undefined)
                                solde=0;
                            if(libele==undefined)
                                libele=" "
                            if(numacc==undefined)
                                numacc=" "
                            if(num==undefined)
                                num=" "
                        const obj = {
                            "Num_Compte": numacc,
                            "date": date,
                            "Numero": num,
                            "libelle":libele,
                            "debit":debit,
                            "credit":credit,
                            "solde": solde,
                            "lettrage":undefined
                        };
                        result.push(obj);
                    }
                    if((debit === undefined || debit === null || debit === ''|| isNaN(debit))&&(credit === undefined || credit === null || credit === ''|| isNaN(credit))&& (!date || isNaN(date)))
                        continue
                    if(!date){
                        resolve("La date dans le Grand livre bancaire ligne"+(i+1)+"est vide !")
                        return;
                    }
                    if((debit === undefined || debit === null || debit === '')&&(credit === undefined || credit === null || credit === ''))
                    {
                        resolve("Le debit et le crédit dans le Grand livre bancaire ligne"+(i+1)+"sont vides !")
                        return;
                    }
                    if(isNaN(debit)&&isNaN(credit))
                    {
                        resolve("Le debit et le crédit dans le Grand livre bancaire ligne"+(i+1)+"doivent être des nombres !")
                        return;
                    }
            }

            reader2.onload = function (e2) {
                const data2 = new Uint8Array(e2.target.result);
                const workbook2 = XLSX.read(data2, {type: 'array'});
                const sheetName2 = workbook2.SheetNames[0];
                const worksheet2 = workbook2.Sheets[sheetName2];
                const jsonData = XLSX.utils.sheet_to_json(worksheet2, { header: 1 });
                const firstRowsReleve=XLSX.utils.sheet_to_json(worksheet2, { header: 1, range: "A1:Z2" });
                if(firstRowsReleve.length<2)
                    {resolve("Veuillez charger le même template du releve bancaire !");
                     return;
                    }
                for(i=0;i<firstRowsReleve.length;i++)
                    {
                        if(notEqualTo(firstRowsReleve[i],releveTwoRows[i]))
                            {
                                resolve("Veuillez charger le même template du releve bancaire !");
                                return;
                            }
                    }
                let result_releve=[];
                for(i=2;i<jsonData.length;i++){
                    let libele = jsonData[i][1];
                    let date = jsonData[i][0];
                    let debit=jsonData[i][4];
                    let credit=jsonData[i][5];
                    let solde = jsonData[i][6];
                    // Check if date or montant is missing
                    if (date && (debit || credit)) {
                        if(debit==undefined)
                             debit=0;
                             if(credit==undefined)
                                     credit=0;
                               if(num==undefined)
                                    num=" "
                                if(libele==undefined)
                                 libele=" "
                                if(solde==undefined)
                                    solde=0
                                if(numacc==undefined)
                                    numacc=" "
                        const obj = {
                            "date": date,
                            "Operation":libele,
                            "debit":debit,
                            "credit":credit,
                            "solde": solde,
                            "lettrage":undefined
                        };
                        result_releve.push(obj);
                    }
                    if((debit === undefined || debit === null || debit === ''|| isNaN(debit))&&(credit === undefined || credit === null || credit === ''|| isNaN(credit))&& (!date || isNaN(date)))
                        continue
                    if(!date){
                        resolve("La date dans le releve bancaire ligne"+(i+1)+"est vide !")
                        return;
                    }
                    if((debit === undefined || debit === null || debit === '')&&(credit === undefined || credit === null || credit === ''))
                    {
                        resolve("Le debit et le crédit dans le releve bancaire ligne"+(i+1)+"sont vides !")
                        return;
                    }
                    if(isNaN(debit)&&isNaN(credit))
                    {
                        resolve("Le debit et le crédit dans le releve bancaire ligne"+(i+1)+"doivent être des nombres !")
                        return;
                    }
                }
                const res=same(result,result_releve,response.data);
                resolve(res);
            }
            reader2.readAsArrayBuffer(releve)
        };
        reader.readAsArrayBuffer(livre);

    });
}
function notEqualTo(arr1,arr2){
    if(arr1.length!=arr2.length)
        return true;
    for(i=0;i<arr1.length;i++)
        if(arr1[i]!=arr2[i])
            return true
    return false;
}

function valueToDate(value) {
    const milliseconds = (value - 25569) * 86400 * 1000;
    const date = new Date(milliseconds);
    const year = date.getUTCFullYear();
    const month = ('0' + (date.getUTCMonth() + 1)).slice(-2);
    const day = ('0' + date.getUTCDate()).slice(-2);
    const dateString = year + '-' + month + '-' + day;
    return dateString;
}
