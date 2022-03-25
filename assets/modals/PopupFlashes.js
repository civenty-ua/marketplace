export default class PopupFlashes{
    #queue = [];

    addToQueue(Popup){
        if (typeof Popup === 'object') {
            this.#queue.push(Popup);
        }
    }
    show(){
        if(this.#queue.length < 1) return;

        this.#callQueueElement();
        this.#queue[0].show()
    }

    #callQueueElement( index = 0){
        index++;
        if(index < this.#queue.length){
            this.#queue[(index - 1)].on('onHide', () =>{
                this.#queue[index].show();
            });
            this.#callQueueElement(index)
        }
    }
}