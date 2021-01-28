# Developing the Fields API

## Abstract Syntax Tree

The field collection is an Abstract Syntax Tree (AST) representation of fields (and/or nested collections).

### Nodes and Polymorphism

The field collection leverages Polymorphism to provide a single interface for the collected field nodes and nested collection nodes.

> Polymorphism is the provision of a single interface to entities of different types. <sup>[1](https://www.stroustrup.com/glossary.html#Gpolymorphism)</sup>

Each item in a collection implements the `Node` interface, with nested collections implementing the extended `GroupNode` interface to represent a group of Nodes. Each node, whether a field or a nested collection can be reference the same way. Collection method parameters are type hinted with `Node` which support either a filed or a collection.

