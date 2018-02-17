Blocks
=======

Want to add new blocks to Give this document outlines guidelines for the same.


## Structure

Following outlines the files & folders

```
├── blocks
│   ├── components
│   │   ├── my-component
│   │   │   ├── index.js
│   │   │   ├── style.scss
│   │   │   ├── REAMDE.md                
│   ├── my-block
│   │   ├── data
│   │   │   ├── attributes.js
│   │   │   ├── icons.js
│   │   │   ├── options.js
│   │   ├── edit
│   │   │   ├── block.js
│   │   │   ├── controls.js
│   │   │   ├── inspector.js
│   │   ├── class-my-block.php
│   │   ├── index.js
│   │   ├── style.scss
└── load.js
```

## load.js

Main entry file responsible for loading various blocks, each new block needs to be added here.

### example

```
import '/my-block/index'
```  

## my-block

This directory includes all the files that makeup block.

Following outlines the possible structure.

```
├── my-block
│   ├── data
│   │   ├── attributes.js
│   │   ├── icons.js
│   │   ├── options.js
│   ├── edit
│   │   ├── block.js
│   │   ├── controls.js
│   │   ├── inspector.js
│   ├── class-my-block.php
│   ├── index.js
│   ├── style.scss 
```

**data**

Various files for data that can be used across the block. 

**attributes.js**

To keep code modular and avoid one huge fat file block attributes can be extracted to its own file.

**icon(s).js**

Svg icon data object for block 

**options.js**

array/object for dropdown(s)

## components

This directory includes a library of generic React components to be used for creating common UI elements shared between blocks.
Identify and extract reusable compnents as much possible.

**my-component** 

Each component is organized in its parent folder to hold various files & folder.

Following outlines the possible structure.

```
├── my-component
│   ├── index.js
│   ├── style.scss
│   ├── REAMDE.md  
```

**index.js**

Its the main file building the component, a component can be made of a single file or multiple files.
In case the component is complex it can be split across files and index.js serves as a loader.

**style.scss**

All the styling required by the component 

**README.md**

Each component added should ship the documentation stating usage & example.

## Note

Based on the requirement may contain other files & folder.  