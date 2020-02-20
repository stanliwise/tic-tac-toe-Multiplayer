function bit2array(bitNum){
    let arraybit = [];
    for (let index = 0; index < 9; index++) {
        index2power = Math.pow(2, index);
        if((bitNum & index2power) == index2power)
               arraybit.push(index + 1);
    }

    return arraybit;
}

console.log(bit2array(256+1+4))

console.log(Math.pow(2,8));