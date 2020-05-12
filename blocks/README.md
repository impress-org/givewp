Blocks
=======

This document outlines guidelines for adding additional blocks to the GiveWP plugin.

## Structure

The following outlines the files and directory structure:

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

## :page_facing_up: load.js

This is the main entry file responsible for loading various blocks, each new block needs to be added here.

### Example

```
import '/my-block/index'
```  

## :open_file_folder:  components

This directory includes a library of generic React components to be used for creating common UI elements shared between blocks. Identify and extract reusable components as much possible.

**:open_file_folder: my-component** 

Each component will be organized in its parent folder to hold various files: page_facing_up: & folder.

Following outlines the possible structure.

```
├── my-component
│   ├── index.js
│   ├── style.scss
│   ├── REAMDE.md  
```

**:page_facing_up: index.js**

This is the main script building the component. A component can be made of a single file or multiple files.
In the case that the component is more complex it can be split across files and index.js serves as a loader.

**:page_facing_up: style.scss**

All the styling required by the component.

**:page_facing_up: README.md**

Each component added should ship the documentation stating usage and at least one example.

## :open_file_folder: my-block

This directory includes all the files that makeup a block.

The following outlines the possible structure.

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

**:open_file_folder: data**

Various files for data that can be used across the block. 

**:page_facing_up: attributes.js**

To keep code modular and avoid one huge fat file block attributes can be extracted to its own file.

**:page_facing_up: icon(s).js**

Svg icon data object for block 

**:page_facing_up: options.js**

array/object for dropdown(s)

**:open_file_folder: data**

Various files for edit UI of block

**:page_facing_up: block.js**

Main block component class or render function for edit UI.

**:page_facing_up: controls.js**

BlockControls extracted to files as wrapper component.

**:page_facing_up: inspector.js**

Inspector controls extracted to files as wrapper component.

## Note

Based on the requirement this may contain other files & folders.
